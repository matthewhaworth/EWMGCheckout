<?php

class Vortex_Checkout_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function designAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function styleguideAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}