<?php namespace App\Services\Ticketing;

use App\Tag;
use App\User;
use Carbon\Carbon;
use Common\Auth\UserRepository;
use Common\Files\FileEntry;
use GuzzleHttp\Client;
use App\Services\TagRepository;
use Illuminate\Support\Collection;
use App\Services\Mail\EmailBodyParser;

class HelpScoutImporter
{
    /**
     * @var Client
     */
    private $http;

    /**
     * Base url for HelpScout API.
     *
     * @var string
     */
    private static $base = 'https://api.helpscout.net/v1/';

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var ReplyRepository
     */
    private $replyRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var FileEntry
     */
    private $uploads;

    /**
     * @var EmailBodyParser
     */
    private $emailBodyParser;

    /**
     * Cached user profiles from HelpScout API.
     *
     * @var array
     */
    private $userCache = [];

    /**
     * HelpScoutImporter constructor.
     *
     * @param Client $http
     * @param TicketRepository $ticketRepository
     * @param ReplyRepository $replyRepository
     * @param UserRepository $userRepository
     * @param TagRepository $tagRepository
     * @param FileEntry $uploads
     * @param EmailBodyParser $emailBodyParser
     */
    public function __construct(
        Client $http,
        TicketRepository $ticketRepository,
        ReplyRepository $replyRepository,
        UserRepository $userRepository,
        TagRepository $tagRepository,
        FileEntry $uploads,
        EmailBodyParser $emailBodyParser
    )
    {
        $this->http = $http;
        $this->uploads = $uploads;
        $this->tagRepository = $tagRepository;
        $this->userRepository = $userRepository;
        $this->emailBodyParser = $emailBodyParser;
        $this->replyRepository = $replyRepository;
        $this->ticketRepository = $ticketRepository;

        ini_set('max_execution_time', 0);
    }

    /**
     * Import HelpScout conversations as tickets.
     */
    public function importConversations()
    {
        $id = $this->getMailboxId();
        $response = $this->call("mailboxes/$id/conversations.json");

        for ($i = 1; $i <= $response['pages']; $i++) {
            $this->feedback("page $i of {$response['pages']}");
            $response = $this->call("mailboxes/$id/conversations.json", ['page' => $i]);

            $tickets = collect($response['items'])->map(function($conversation) {
                return $this->transformConversation($conversation);
            });

            $this->createTickets($tickets);
        }
    }

    /**
     * Create tickets from HelpScout conversations data.
     *
     * @param Collection $ticketsData
     * @return Collection
     */
    private function createTickets($ticketsData)
    {
        return $ticketsData->map(function($ticketData) {
            return $this->createTicket($ticketData);
        });
    }

    /**
     * Create full ticket from HelpScout conversation data.
     *
     * @param array $ticketData
     * @return \App\Ticket
     */
    private function createTicket($ticketData)
    {
        //last reply will be the one that created conversation on HelpScout
        $lastReply = $ticketData['replies']->splice(-1)->first();

        $ticket = $this->ticketRepository->create([
            'user_id'  => $this->createUser($ticketData['creator'])->id,
            'subject'  => $ticketData['subject'],
            'category' => $this->createTags($ticketData['tags']),
            'body'     => $lastReply['body'],
            'uploads'  => $this->createUploads($lastReply['uploads'], $this->createUser($lastReply['creator'])->id)->pluck('file_name'),
        ], false);

        //update timestamps for automatically created reply,
        //so it doesn't end up being first in conversation
        $ticket->replies->first()->created_at = $lastReply['created_at'];
        $ticket->replies->first()->updated_at = $lastReply['created_at'];
        $ticket->replies->first()->save();

        $this->tagRepository->attachByName($ticket, $ticketData['status']);

        $ticketData['replies']->each(function($replyData) use($ticket) {
            $user = $this->createUser($replyData['creator']);

            $reply = $this->replyRepository->create([
                'body'    => $replyData['body'],
                'user_id' => $user->id,
                'uploads' => $this->createUploads($replyData['uploads'], $user->id)->pluck('file_name')
            ], $ticket, 'replies');

            $reply->created_at = $replyData['created_at'];
            $reply->updated_at = $replyData['created_at'];
            $reply->save();
        });

        $ticket->created_at = $ticketData['created_at'];
        $ticket->updated_at = $ticketData['updated_at'];
        $ticket->closed_at  = $ticketData['closed_at'];
        $ticket->save();

        return $ticket;
    }

    /**
     * Create uploads from specified HelpScout attachments.
     *
     * @param Collection $attachments
     * @param int $userId
     * @return Collection
     */
    private function createUploads($attachments, $userId)
    {
        $uploads = $attachments->map(function($uploadData) use($userId) {
            $contents = $this->getRemoteAttachmentContents($uploadData['id']);
            $uploadData['user_id']  = $userId;
            $uploadData['contents'] = $contents;
            return $uploadData;
        })->filter()->toArray();

        return $this->uploads->store($uploads);
    }

    /**
     * Find or create tags matching specified names.
     *
     * @param array $names
     * @return array
     */
    private function createTags($names)
    {
        return app(Tag::class)->insertOrRetrieve($names, 'category')->pluck('id')->toArray();
    }

    /**
     * Find existing user or create a new one.
     *
     * @param array $userData
     * @return User
     */
    private function createUser($userData)
    {
        $user = User::where('email', $userData['email'])->first();

        if ( ! $user) {
            $user = $this->userRepository->create($userData);
        }

        return $user;
    }

    /**
     * Transform HelpScout conversation into ticket.
     *
     * @param array $conversation
     * @return array
     */
    private function transformConversation($conversation)
    {
        $updatedAt = isset($conversation['userModifiedAt']) ? $conversation['userModifiedAt'] : $conversation['modifiedAt'];

        return [
            'subject' => isset($conversation['subject']) ? $conversation['subject'] : '(no subject)',
            'created_at' => Carbon::parse($conversation['createdAt']),
            'updated_at' => Carbon::parse($updatedAt),
            'closed_at' => $conversation['closedAt'] ? Carbon::parse($conversation['closedAt']) : null,
            'creator' => $this->transformUser($conversation['createdBy']),
            'tags' => $conversation['tags'],
            'status' => $conversation['status'] == 'active' ? 'open' : $conversation['status'],
            'replies' => $this->transformThreads($conversation['id']),
        ];
    }

    /**
     * Get formatted replies for specified conversation.
     *
     * @param string $id
     * @return Collection
     */
    private function transformThreads($id)
    {
        $conversation = $this->getConversation($id);

        $threads = collect($conversation['threads'])->filter(function($item) {
            return $item['type'] === 'customer' || $item['type'] === 'message';
        });

        return $threads->map(function($item) {
            return [
                'created_at' => Carbon::parse($item['createdAt']),
                'body'       => $this->emailBodyParser->parse($item['body']),
                'creator'    => $this->transformUser($item['createdBy']),
                'uploads'    => $this->transformAttachments($item['attachments']),
            ];
        });
    }

    /**
     * Format HelpScout attachments array.
     *
     * @param array $attachments
     * @return Collection
     */
    private function transformAttachments($attachments)
    {
        if ( ! $attachments) return collect();

        return collect($attachments)->map(function($attachment) {
            return [
                'id'            => $attachment['id'],
                'mime_type'     => $attachment['mimeType'],
                'original_name' => $attachment['fileName'],
                'size'          => $attachment['size'],
            ];
        });
    }

    /**
     * Format HelpScout user array.
     *
     * @param array $user
     * @return array
     */
    private function transformUser($user)
    {
        //fetch user profile from API or cache
        if (isset($this->userCache[$user['email']])) {
            $profile = $this->userCache[$user['email']];
        } else {
            $uri = $user['type'] == 'customer' ? 'customers' : 'users';
            $profile = $this->call($uri.'/'.$user['id'].'.json')['item'];
            $this->userCache[$user['email']] = $profile;
        }

        //get avatar, unless it's a gravatar url
        if ($profile['photoUrl'] && ! \Str::contains($profile['photoUrl'], 'gravatar')) {
            $avatar = $profile['photoUrl'];
        }

        return [
            'email' => $user['email'],
            'first_name' => $profile['firstName'] ? $profile['firstName'] : null,
            'last_name'  => $profile['lastName'] ? $profile['lastName'] : null,
            'avatar'     => isset($avatar) ? $avatar : null,
            'timezone'   => isset($profile['timezone']) ? $profile['timezone'] : null,
            'country'    => isset($profile['location']) ? last(explode(',', $profile['location'])): null,
            'created_at' => $profile['createdAt'] ? Carbon::parse($profile['createdAt']): null,
            'updated_at' => $profile['modifiedAt'] ? Carbon::parse($profile['modifiedAt']): null,
        ];
    }

    /**
     * Get specified attachment contents via HelpScout API.
     *
     * @param string $id
     * @return string
     */
    private function getRemoteAttachmentContents($id)
    {
        return base64_decode($this->call('attachments/'.$id.'/data.json')['item']['data']);
    }

    /**
     * Get conversation matching specified ID via HelpScout API.
     *
     * @param string $id
     * @return array
     */
    private function getConversation($id)
    {
        return $this->call("conversations/$id.json")['item'];
    }

    /**
     * Get first available mailbox via HelpScout API.
     *
     * @return array
     */
    private function getMailboxId()
    {
        return $this->call('mailboxes.json')['items'][0]['id'];
    }

    /**
     * Call given HelpScout API uri.
     *
     * @param string  $uri
     * @param array   $params
     * @return array
     */
    public function call($uri, $params = [])
    {
        $credentials = base64_encode(env('HELPSCOUT_API_KEY').':X');

        $response = $this->http->request('GET', self::$base.$uri, [
            'headers' => ['Authorization' => 'Basic ' . $credentials],
            'query'   => $params,
            'http_errors' => false,
            'verify' => false,
        ]);

        if ($response->getStatusCode() === 429) {
            sleep($response->getHeaderLine('Retry-After'));
            return $this->call($uri, $params);
        }

        return json_decode($response->getBody()->getContents(), true) ?: [];
    }

    public function feedback($msg)
    {
        $msg = $msg."<br>";
        echo $msg;
        flush();

        $levels = ob_get_level();
        for ($i=0; $i<$levels; $i++)
            ob_end_flush();
    }
}
