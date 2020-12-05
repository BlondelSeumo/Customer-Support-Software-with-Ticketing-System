<?php

use App\Action;
use App\Operator;
use App\Condition;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class TriggersTableSeeder extends Seeder
{
    /**
     * Operator model instance.
     *
     * @var Operator
     */
    private $operator;

    /**
     * Condition model instance.
     *
     * @var Condition
     */
    private $condition;

    /**
     * Action model instance.
     *
     * @var Action
     */
    private $action;

    public $operators = [
        0 => ['name' => 'contains', 'display_name' => 'Contains', 'type' => 'primitive'],
        1 => ['name' => 'not_contains', 'display_name' => 'Does not contain', 'type' => 'primitive'],
        2 => ['name' => 'starts_with', 'display_name' => 'Starts with', 'type' => 'primitive'],
        3 => ['name' => 'ends_with', 'display_name' => 'Ends with', 'type' => 'primitive'],
        4 => ['name' => 'equals', 'display_name' => 'Equals', 'type' => 'primitive'],
        5 => ['name' => 'not_equals', 'display_name' => 'Does not equal', 'type' => 'primitive'],
        6 => ['name' => 'matches_regex', 'display_name' => 'Matches regex pattern', 'type' => 'primitive'],
        7 => ['name' => 'more', 'display_name' => 'More then', 'type' => 'primitive'],
        8 => ['name' => 'less', 'display_name' => 'Less then', 'type' => 'primitive'],
        9 => ['name' => 'is', 'display_name' => 'Is', 'type' => 'primitive'],
        10 => ['name' => 'not', 'display_name' => 'Is not', 'type' => 'primitive'],
        11 => ['name' => 'changed', 'display_name' => 'Changed', 'type' => 'mixed'],
        12 => ['name' => 'changed_to', 'display_name' => 'Changed to', 'type' => 'mixed'],
        13 => ['name' => 'changed_from', 'display_name' => 'Changed from', 'type' => 'mixed'],
        14 => ['name' => 'not_changed', 'display_name' => 'Not changed', 'type' => 'mixed'],
        15 => ['name' => 'not_changed_to', 'display_name' => 'Not changed to', 'type' => 'mixed'],
        16 => ['name' => 'not_changed_from', 'display_name' => 'Not changed from', 'type' => 'mixed'],
    ];

    public $conditions = [
        'event:type'          => ['type' => 'event:type', 'name' => 'Event: Type', 'operators' => [9, 10]],
        'ticket:subject'      => ['type' => 'ticket:subject', 'name' => 'Ticket: Subject', 'operators' => [0,1,2,3,4,5,6]],
        'ticket:body'         => ['type' => 'ticket:body', 'name' => 'Ticket: Body', 'operators' => [0,1,2,3]],
        'ticket:status'       => ['type' => 'ticket:status', 'name' => 'Ticket: Status', 'operators' => [9, 10, 11, 12, 13, 14, 15, 16]],
        'ticket:category'     => ['type' => 'ticket:category', 'name' => 'Ticket: Category', 'operators' => [0,1,2,3,4,5,6,9,10]],
        'ticket:uploads'      => ['type' => 'ticket:uploads', 'name' => 'Ticket: Number of Attachments', 'operators' => [4, 5, 7, 8]],
        'ticket:assignee'     => ['type' => 'ticket:assignee', 'name' => 'Ticket: Assignee', 'operators' => [9, 10, 11, 12, 13, 14, 15, 16]],
        'customer:name'       => ['type' => 'customer:name', 'name' => 'Customer: Name', 'operators' => [0,1,2,3,4,5,6]],
        'customer:email'      => ['type' => 'customer:email', 'name' => 'Customer: Email', 'operators' => [0,1,2,3,4,5,6]],
    ];

    public $actions = [
        ['display_name' => 'Ticket: Move to Category', 'name' => 'move_ticket_to_category', 'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Notify: via Email',        'name' => 'send_email_to_user',      'updates_ticket' => 0, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Add a note',       'name' => 'add_note_to_ticket',      'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Change status',    'name' => 'change_ticket_status',    'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Assign to Agent',  'name' => 'assign_ticket_to_agent',  'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Add tag(s)',       'name' => 'add_tags_to_ticket',      'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Remove tag(s)',    'name' => 'remove_tags_from_ticket', 'updates_ticket' => 1, 'aborts_cycle' => 0],
        ['display_name' => 'Ticket: Delete',           'name' => 'delete_ticket',           'updates_ticket' => 1, 'aborts_cycle' => 1],
    ];

    public $actionInputConfigs = [
        'move_ticket_to_category' => ['inputs' => [['type' => 'select', 'select_options' => 'category:tags', 'name' => 'category_name']]],
        'add_note_to_ticket'      => ['inputs' => [['type' => 'textarea', 'placeholder' => 'Note Text', 'name' => 'note_text']]],
        'change_ticket_status'    => ['inputs' => [['type' => 'select', 'default_value' => 'open', 'select_options' => 'ticket:status', 'name' => 'status_name']]],
        'assign_ticket_to_agent'  => ['inputs' => [['type' => 'select', 'default_value' => '(current user)', 'select_options' => 'agent:id', 'name' => 'agent_id']]],
        'add_tags_to_ticket'      => ['inputs' => [['type' => 'text', 'placeholder' => 'Separate tags with comma', 'name' => 'tags_to_add']]],
        'remove_tags_from_ticket' => ['inputs' => [['type' => 'text', 'placeholder' => 'Separate tags with comma', 'name' => 'tags_to_remove']]],
        'delete_ticket'           => ['inputs' => []],
        'send_email_to_user'      => ['inputs' => [
            ['type' => 'select', 'name' => 'agent_id', 'default_value' => '(current user)', 'select_options' => 'agent:id'],
            ['placeholder' => 'Subject', 'type' => 'text', 'name' => 'subject'],
            ['placeholder' => 'Email Message', 'type' => 'textarea', 'name' => 'message']]
        ]
    ];

    /**
     * @param Operator  $operator
     * @param Condition $condition
     * @param Action    $action
     */
    public function __construct(Operator $operator, Condition $condition, Action $action)
    {
        $this->operator  = $operator;
        $this->condition = $condition;
        $this->action    = $action;
    }

    /**
     * Seed conditions table with all available
     * workflow conditions.
     *
     * @return void
     */
    public function run()
    {
        $operators = $this->seedOperators();

        $this->seedConditions($operators);
        $this->seedActions();
    }

    /**
     * Seed actions table from static actions names.
     */
    private function seedActions()
    {
        $this->action->truncate();

        $actions = array_map(function($action) {
            $action['input_config'] = json_encode($this->actionInputConfigs[$action['name']]);
            return $action;
        }, $this->actions);

        $this->action->insert($actions);
    }

    /**
     * Seed operators table from static operator names.
     *
     * @return Collection
     */
    private function seedOperators()
    {
        $this->operator->truncate();

        $this->operator->insert(array_values($this->operators));

        return $this->operator->get();
    }

    /**
     * Seed conditions table from static conditions configuration.
     *
     * @param Collection $operators
     */
    private function seedConditions(Collection $operators)
    {
        DB::table('conditions')->truncate();
        DB::table('condition_operator')->truncate();

        //seed conditions
        $this->condition->insert(array_map(function($condition) {
            return ['name' => $condition['name'], 'type' => $condition['type']];
        }, $this->conditions));

        //generate insert for attaching operators to conditions in one query
        $pivot = $this->condition->get()->map(function($condition) use($operators) {

            //get indexes of this conditions operators
            $indexes = $this->conditions[$condition['type']]['operators'];

            //get names of this conditions operators
            $names   = collect($indexes)->map(function($index) {
                return $this->operators[$index]['name'];
            });

            //compile many to many pivot table insert to attach all operators to this condition
            return $operators->whereIn('name', $names)->pluck('id')->map(function($id) use($condition) {
                return ['condition_id' => $condition->id, 'operator_id' => $id];
            });
        })->flatten(1)->toArray();

        DB::table('condition_operator')->insert($pivot);
    }
}
