<?php
class Buddha_Base_Component extends Buddha_Base_Object
{
    /**
     * @var array the attached event handlers (event name => handlers)
     */
    private $_events = array();
    /**
     * @var Behavior[]|null the attached behaviors (behavior name => behavior). This is `null` when not initialized.
     */
    private $_behaviors;

}