@foreach($ticket->latest_replies as $reply)
    ## {{$reply->user->display_name}} replied, on {{$reply->created_at_formatted}}:

    {{ strip_tags($reply->body) }}

    -----------------------------------------------------------

@endforeach

{{$reference}}