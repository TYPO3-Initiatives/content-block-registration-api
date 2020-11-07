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
            'eval' => 'trim'
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
            'eval' => 'trim'
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
            'eval' => 'trim,int,nospace'
        ]
    ],
    'money' => [
        'label' => 'Money',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,double2,nospace'
        ]
    ],
    'number' => [
        'label' => 'Number',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,num,nospace'
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
            'eval' => 'trim,double2,nospace'
        ]
    ],
    'url' => [
        'label' => 'Url',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            'autocomplete' => true,
            'default' => 'Default value',
            'linkPopup' => [
                'allowedExtensions' => 'pdf',
                'blindLinkFields' => 'target,title',
                'blindLinkOptions' => 'folder,spec,telefone,mail',
                'windowOpenParameters' => 'height=800,width=600'
            ],
            'max' => 15,
            'placeholder' => 'Placeholder text',
            'size' => 20,
            'eval' => 'trim,nospace,lower'
        ]
    ],
    'tel' => [
        'label' => 'Tel',
        'config' => [
            'type' => 'input',
            'autocomplete' => true,
            'default' => 0,
            'size' => 20,
            'eval' => 'trim,alphanum'
        ]
    ],
    'color' => [
        'label' => 'Color',
        'config' => [
            'type' => 'input',
            'renderType' => 'colorpicker',
            'autocomplete' => true,
            'default' => '#fff',
            'size' => 20,
            'valuePicker' => [
                'items' => [
                    ['#FF0000', 'Red'],
                    ['#008000', 'Green'],
                    ['#0000FF', 'Blue']
                ],
            ],
            'eval' => 'trim,alphanum'
        ]
    ],
    'date' => [
        'label' => 'Date',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'default' => mktime(),
            'disableAgeDisplay' => false,
            'size' => 20,
            'range' => [
                'lower' => mktime(0, 0, 0, 1, 1, 1970),
                'upper' => mktime(0, 0, 0, 12, 31, 2020),
            ],
            'eval' => 'date'
        ]
    ],
    'datetime' => [
        'label' => 'DateTime',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'default' => mktime(),
            'disableAgeDisplay' => false,
            'size' => 20,
            'range' => [
                'lower' => mktime(0, 1, 0, 1, 1, 1970),
                'upper' => mktime(23, 59, 0, 12, 31, 2020),
            ],
            'eval' => 'datetime'
        ]
    ],
    'time' => [
        'label' => '',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'default' => mktime(),
            'disableAgeDisplay' => false,
            'size' => 20,
            'range' => [
                'lower' => mktime(0, 1, 0, 0, 0, 0),
                'upper' => mktime(23, 59, 0,0, 0, 0),
            ],
            'eval' => 'time'
        ]
    ],
];
