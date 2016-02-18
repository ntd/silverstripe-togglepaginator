<?php

/**
 * A GridField component that allows to temporary disable the
 * pagination.
 *
 * It should be included before the GridFieldPaginator component that
 * must be present (otherwise this class would be pretty useless).
 *
 * @package silverstripe-togglepaginator
 */
class GridFieldTogglePaginator implements GridField_HTMLProvider, GridField_ActionProvider, GridField_DataManipulator
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
     * Implements the GridField_HTMLProvider interface.
     *
     * @param  GridField $grid
     * @return array
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

    /**
     * Required by the GridField_ActionProvider interface.
     *
     * @param  GridField $grid
     * @return array
     */
    public function getActions($grid)
    {
        return array('toggle');
    }

    /**
     * Handle the action.
     *
     * It swithces the "state" flag and saves the data into a session
     * variable for later reuse.
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
            $this->updateState($grid);
            $this->state['active'] = ! $this->state['active'];
            Session::set($this->state_id, $this->state);
            break;

        default:
            throw new InvalidArgumentException(sprintf(
                'Action "%s" not understood',
                $action
            ));
        }
    }

    /**
     * Required by the GridField_DataManipulator interface.
     *
     * Disable pagination on the GridFieldPaginator component, if
     * required by the current "state" flag. It must be called before
     * GridFieldPaginator::getManipulatedData() to take effect, hence
     * the requirement that GridFieldTogglePaginator must be added
     * before GridFieldPaginator.
     *
     * @param  GridField $grid
     * @return SS_List
     */
    public function getManipulatedData(GridField $grid, SS_List $list)
    {
        $this->updateState($grid);
        if (! $this->state['active']) {
            $grid->getConfig()->getComponentByType('GridFieldPaginator')
                ->setItemsPerPage(PHP_INT_MAX);
        }
        return $list;
    }
}
