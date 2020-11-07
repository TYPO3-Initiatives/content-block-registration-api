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
    'email' => [
        'label' => 'Email',
        'config' => [
            'type' => 'input',
            'autocomplete' => true,
            'default' => 'Default value',
            'placeholder' => 'Placeholder text',
            'size' => 20,
            'eval' => 'trim,email,required',
        ]
    ],
    'integer' => [
        'label' => 'Integer',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,int,nospace',
        ]
    ],
    'money' => [
        'label' => 'Money',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,double2,nospace',
        ]
    ],
    'number' => [
        'label' => 'Number',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,num,nospace',
        ]
    ],
    'percent' => [
        'label' => 'Percent',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'range' => [
                'lower' => 0,
                'upper' => 100,
            ],
            'size' => 20,
            'slider' => [
                'step' => 1,
                'width' => 100,
            ],
            'eval' => 'trim,double2,nospace',
        ]
    ],
];
