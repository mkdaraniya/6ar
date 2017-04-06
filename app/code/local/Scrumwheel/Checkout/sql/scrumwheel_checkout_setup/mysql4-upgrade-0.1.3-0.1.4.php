<?php

$this->startSetup();
$this->addAttribute('order', 'sync_status', array(
    'type'          => 'varchar',
    'label'         => 'Sync Status',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'user_defined'  =>  true
));

$this->endSetup();