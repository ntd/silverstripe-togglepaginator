<?php

class GridFieldTogglePaginator implements GridField_HTMLProvider, GridField_ActionProvider
{

    /**
     * The icon to use on the enable pagination button. Can be null.
     * @config
     */
    private static $enable_icon;

    /**
     * The icon to use on the disable pagination button. Can be null.
     * @config
     */
    private static $disable_icon;

    /**
     * Fragment to write the button to.
     */
    protected $target;

    /**
     * ID of the session data.
     */
    protected $state_id;

    /**
     * Session data (an associative array).
     */
    protected $state;


    public function __construct($target = 'buttons-before-right')
    {
        $this->target = $target;
    }

    /**
     * Update $state and $state_id properties.
     *
     * If the $state array does not exist in the session, create it.
     *
     * The current implementation is borrowed directly from
     * GridField_FormAction::getAttributes() 3.2.0-beta2.
     */
    protected function updateState($grid)
    {
        if (isset($this->state_id)) {
            return;
        }

        $state = array(
            'grid'       => $grid->getName(),
            'actionName' => 'toggle',
            'active'     => true,
        );

        // Ensure the id doesn't contain only numeric characters
        $this->state_id = 'gf_' . substr(md5(serialize($state)), 0, 8);

        $this->state = Session::get($this->state_id);
        if (! is_array($this->state) || ! array_key_exists('active', $this->state)) {
            $this->state = $state;
            Session::set($this->state_id, $state);
        }
    }

    /**
     * Update $state and $state_id properties.
     *
     * If the $state array does not exist in the session, create it.
     *
     * The current implementation is borrowed directly from
     * GridField_FormAction::getAttributes() 3.2.0-beta2.
     *
     * @param  GridField $grid The subject GridField instance
     * @return array           An associative array of target => fragment
     */
    public function getHTMLFragments($grid)
    {
        $this->updateState($grid);

        $active = $this->state['active'];
        $params = http_build_query(array('StateID' => $this->state_id));
        $data = new ArrayData(array(
            'state' => $active ? 'ui-state-default' : 'ss-ui-alternate ui-state-highlight',
            'icon'  => Config::inst()->get(__CLASS__, $active ? 'enable_icon' : 'disable_icon'),
            'label' => $active ? _t(__CLASS__ . '.DISABLE', 'Disable') : _t(__CLASS__ . '.ENABLE', 'Enable'),
            'name'  => 'action_gridFieldAlterAction?' . $params,
            'url'   => $grid->Link(),
        ));

        return array(
            $this->target => $data->renderWith(__CLASS__),
        );
    }

    public function getActions($grid)
    {
        return array('toggle');
    }

    /**
     * Handle the action.
     *
     * @param  GridField $grid      The subject GridField instance
     * @param  string    $action    The action name (lowercase!)
     * @param  mixed     $arguments Optional argument(s) for the action
     * @param  array     $data      Form data, if relevant
     *
     * @throws InvalidArgumentException
     */
    public function handleAction(GridField $grid, $action, $arguments, $data)
    {
        switch ($action) {

        case 'toggle':
            $this->handleToggle($grid);
            break;

        default:
            throw new InvalidArgumentException(sprintf(
                'Action "%s" not understood',
                $action
            ));
        }
    }

    public function handleToggle($grid, $request = null)
    {
        $this->updateState($grid);
        $this->state['active'] = ! $this->state['active'];
        Session::set($this->state_id, $this->state);

        $component = $grid->getConfig()->getComponentByType('GridFieldPaginator');
        if ($this->state['active']) {
            $page_size = Config::inst()->get('GridFieldPaginator', 'default_items_per_page');
        } else {
            $page_size = PHP_INT_MAX;
        }
        $component->setItemsPerPage($page_size);
    }
}
