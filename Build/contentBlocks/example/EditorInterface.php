<?php
$columns = [
    'text' => [
        'label' => 'Text',
        'config' => [
            'type' => 'input',
            'autocomplete' => true,
            'default' => 'Default value',
            'max' => 15,
            'placeholder' => 'Placeholder text',
            'size' => 20,
            'eval' => 'trim',
        ]
    ],
    'textarea' => [
        'label' => 'Textarea',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'enableRichtext' => true,
            'max' => 150,
            'placeholder' => 'Placeholder text',
            'richtextConfiguration' => 'default',
            'rows' => 15,
            'eval' => 'trim',
        ]
    ],
];
