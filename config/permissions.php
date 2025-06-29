<?php
// platform\plugins\genie-payment\config\permissions.php
return [
    [
        'name' => 'Genie Payment',
        'flag' => 'genie-payment.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'genie-payment.create',
        'parent_flag' => 'genie-payment.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'genie-payment.edit',
        'parent_flag' => 'genie-payment.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'genie-payment.destroy',
        'parent_flag' => 'genie-payment.index',
    ],
];