<?php

const ACTIONS = [
    'getOrderTracing' => 'отслеживание заказа',
    'getTracingOrderNumber' => 'отследить по трек-номеру л-пост',
    'getTracingOrderNumberReset' => 'повторить попытку',
    'getTracingCostumerNumber' => 'отследить по номеру заказа интернет-магазина',
    'getOrderInfo' => 'информация о заказе',
    'exitMenu' => 'выйти в меню',
    'getTracingCostumerNumberReset' => 'повторить',
    'getInfoDelivery' => 'получить информацию о доставке',
    'getInfoTracingNumberOnPhone' => 'узнать трек-номер л-пост по номеру телефона',
    'contactInfo' => 'контактная информация',
    'goLPostSite' => 'переход на сайт л-пост',
    'getInfoDeliveryOrderNumber' => 'по трек-номеру л-пост',
    'getInfoDeliveryCostumerNumber' => 'по номеру заказа интернет-магазина',
];

require_once('ControllerActualToken.php');
require_once('ControllerLPost.php');
require_once('ControllerTelegramm.php');
require_once('ControllerDB.php');

$data = json_decode(file_get_contents('php://input'), TRUE);

# Обрабатываем ручной ввод или нажатие на кнопку
$data = $data['callback_query'] ? $data['callback_query'] : $data['message'];

# Записываем сообщение пользователя
$message = mb_strtolower(($data['text'] ?? $data['data']),'utf-8');

$checkStateUser = new Telegramm();
$checkStateUser = $checkStateUser->checkLastMessage($data);

if (preg_match ('/^[\p{Cyrillic}\s\-\/start\/menu\/help\/ordertracing\/orderinfo\/contacts]+$/u', $message)) {
    switch ($message){
    // ----------------------------------------Везде используются (начало)--------------------
        case '/start':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Кнопки главного меню:',
                'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа']
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                        // [
                        //     ['text' => 'Управление уведомлениями об актуальном статусе заказа']
                        // ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
                    ];
                    
        break;
        case '/menu':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Кнопки главного меню:',
                'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа'],
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
            ];
        break;
        case ACTIONS['exitMenu']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Кнопки главного меню:',
                'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа'],
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
            ];
        break;
        case '/orderinfo':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Подскажите, что Вас интересует?',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Получить информацию о доставке '],
                            ['text' => 'Узнать трек-номер Л-Пост по номеру телефона'],
                        ],
                        [
                            ['text' => 'Выйти в меню'],
                        ],
                        
                    ]
                ]
            ];
        break;
        case '/ordertracing':
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Пожалуйста, выберите по какому номеру отследить заказа',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отследить по трек-номеру Л-Пост'],
                            ],
                            [
                                ['text' => 'Отследить по номеру заказа интернет-магазина'],
                            ],
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
            ];
        break;
        case '/contacts':
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Выберите, что вас интересует:',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [
                            [
                                ['text' => 'Переход на сайт Л-Пост'],
                                ['text' => 'Пункты самовывоза Л-Пост'],
                            ],
                            [
                                ['text' => 'Узнать номер телефона контакт-центра'],
                            ],
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
                        ];
        break;
        case '/help':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Справочная информация!'.PHP_EOL.'Кнопка “Отслеживание отправления” – «Здесь вы сможете отследить своё отправление по номеру заказа интернет-магазина или трек-номеру L-Post.» 
                          '.PHP_EOL.'Кнопка “Получение информации по заказу” – «Здесь вы сможете получить информацию по статусу доставки Вашего отправления.» 
                          '.PHP_EOL.'Кнопка “Контактная информация” – «Здесь можно ознакомиться с нашей компанией.»
                          '.PHP_EOL.'Внимание!!!'.PHP_EOL.'В зависимости от версии Telegram у некоторых пользователей не отображается клавиатура с кнопками управления ботом. В основном в версии - Telegram Web. Для её вызова необходимо нажать на значок квадрата с четырьмя точками в правой области строки ввода.
                ',
                'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа'],
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
            ];
        break;
    // ----------------------------------------Везде используются (конец)--------------------
    // ----------------------------------------информация о заказе (начало)--------------------
    case ACTIONS['contactInfo']:
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Выберите, что вас интересует:',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [
                            [
                                ['text' => 'Переход на сайт Л-Пост'],
                                ['text' => 'Пункты самовывоза Л-Пост'],
                            ],
                            [
                                ['text' => 'Узнать номер телефона контакт-центра'],
                            ],
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
                        ];
        break;
        case ACTIONS['getOrderInfo']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Подскажите, что Вас интересует?',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Получить информацию о доставке '],
                            ['text' => 'Узнать трек-номер Л-Пост по номеру телефона'],
                        ],
                        [
                            ['text' => 'Выйти в меню'],
                        ],
                        
                    ]
                ]
            ];
        break;
        case ACTIONS['getInfoDelivery']:
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Пожалуйста, выберите по какому номеру узнать информацию по отправлению',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'По трек-номеру Л-Пост'],
                            ],
                            [
                                ['text' => 'По номеру заказа интернет-магазина'],
                            ],
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
            ];
        break;
        case ACTIONS['getInfoDeliveryOrderNumber']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Введите трек-номер заказа, например, ABC12345678. Пожалуйста, обратите внимание, что трек-номер состоит из 3 букв и 8 цифр.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getInfoDeliveryOrderNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);   
            // file_put_contents(__DIR__ . '/state.json', json_encode($data), FILE_APPEND );
        break;
        case ACTIONS['getInfoDeliveryOrderNumberReset']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Введите трек-номер заказа, например, ABC12345678. Пожалуйста, обратите внимание, что трек-номер состоит из 3 букв и 8 цифр.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getInfoDeliveryOrderNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);        
        break;
        case ACTIONS['getInfoDeliveryCostumerNumber']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Пожалуйста, введите номер заказа интернет-магазина.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getInfoDeliveryCostumerNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);  
        break;
        case ACTIONS['getInfoDeliveryCostumerNumberReset']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Пожалуйста, введите номер заказа интернет-магазина.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getInfoDeliveryCostumerNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);    
        break;
        // ---------------
        case ACTIONS['getInfoTracingNumberOnPhone']:
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Пожалуйста, введите номер телефона получателя в формате 9053455665 (10 цифр). Номер телефона нужен, чтобы идентифицировать Ваше отправление',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
            ];
            $name_comand = 'getInfoTracingNumberOnPhone';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand); 
        break;
        // ----------------------------------------информация о заказе (конец)--------------------
        case ACTIONS['getOrderTracing']:
            $method = 'sendMessage';
            $send_data = [
                    'text' => 'Пожалуйста, выберите по какому номеру отследить заказа',
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отследить по трек-номеру Л-Пост'],
                            ],
                            [
                                ['text' => 'Отследить по номеру заказа интернет-магазина'],
                            ],
                            [
                                ['text' => 'Выйти в меню']
                            ]
                        ]
                    ]
            ];
        break;
        case ACTIONS['getTracingOrderNumber']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Введите трек-номер заказа, например, ABC12345678. Пожалуйста, обратите внимание, что трек-номер состоит из 3 букв и 8 цифр.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getTracingOrderNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);   
            // file_put_contents(__DIR__ . '/state.json', json_encode($data), FILE_APPEND );
        break;
        case ACTIONS['getTracingOrderNumberReset']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Введите трек-номер заказа, например, ABC12345678. Пожалуйста, обратите внимание, что трек-номер состоит из 3 букв и 8 цифр.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getTracingOrderNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);        
            // file_put_contents(__DIR__ . '/state.json', json_encode($data), FILE_APPEND );
        break;
        case ACTIONS['getTracingCostumerNumber']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Пожалуйста, введите номер заказа интернет-магазина.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getTracingCostumerNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);        
            // file_put_contents(__DIR__ . '/state.json', json_encode($data), FILE_APPEND );
        break;
        case ACTIONS['getTracingCostumerNumberReset']:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Пожалуйста, введите номер заказа интернет-магазина.',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Выйти в меню'],
                        ]
                    ]
                ]
            ];
            $name_comand = 'getTracingCostumerNumber';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand);        
            // file_put_contents(__DIR__ . '/state.json', json_encode($data), FILE_APPEND );
        break;
        case ACTIONS['goLPostSite']:
            $method = 'sendMessage';
            $send_data = [
                    'text' => "https://l-post.ru/",
                    'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа'],
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
            ];
        break;
        case 'пункты самовывоза л-пост':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'https://l-post.ru/map',
                'reply_markup' => [
                    'keyboard' => [
                        [
                            ['text' => 'Отслеживание заказа'],
                        ],
                        [
                            ['text' => 'Информация о заказе']
                        ],
                        [
                            ['text' => 'Контактная информация']
                        ],
                    ],
                    'resize_keyboard' => TRUE,
                ]
            ];
        break;
        case 'узнать номер телефона контакт-центра':
        $method = 'sendMessage';
        $send_data = [
                'text' => 
            'Контакт-центр Л-Пост:'. PHP_EOL . 
            '8(800)-700-10-06. Круглосуточно',
            'reply_markup' => [
                'keyboard' => [
                    [
                        ['text' => 'Отслеживание заказа'],
                    ],
                    [
                        ['text' => 'Информация о заказе']
                    ],
                    [
                        ['text' => 'Контактная информация']
                    ],
                ],
                'resize_keyboard' => TRUE,
            ]
                    ];
        break;    
        case 'управление уведомлениями об актуальном статусе заказа':
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Подскажите, что Вас интересует?',
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'keyboard' => [ 
                        [
                            ['text' => 'Подключить функцию '],
                            ['text' => 'Отключить функцию'],
                        ],
                        [
                            ['text' => 'Выйти в меню'],
                        ],
                        
                    ]
                ]
            ];
        break;
        default:
            $method = 'sendMessage';
            $send_data = [
                'text' => 'Не понимаю о чем вы :('
            ];
    }
}
else{
        $lpost = new LPost();
        $db = new DB();
        $regular = '/^(?i)[ABC]{3}[0-9]{8}$|^(?i)[АВС]{3}[0-9]{8}$/u';
        $regular_only_obrezka = '/^[ABCАВС]{3}/u';
        if($checkStateUser["name_comand"] == "getTracingOrderNumber"){
            $message = mb_strtoupper(($data['text'] ?? $data['data']),'utf-8');
            if(preg_match_all($regular, $message)){
                $message = preg_replace($regular_only_obrezka, '', $message);
                $json = $lpost->getOrderInfo(...['OrderNumber' => $message]);
                $shablon = $lpost->parseJsonTrackingOrder($data,$json,$message);
                $method = 'sendMessage';
                if (empty($shablon["success"])){
                    $send_data = ['text' => \implode('', $shablon["shablon"])];
                    // $send_data = ['text' => $json];
                    
                }
                else{
                    $send_data = [
                        'text' => \implode('', $shablon["shablon"]),
                        'reply_markup' => [
                            'resize_keyboard' => true,
                            'keyboard' => [ 
                                [
                                    ['text' => 'Отслеживание заказа']
                                ],
                                [
                                    ['text' => 'Информация о заказе']
                                ],
                                [
                                    ['text' => 'Контактная информация']
                                ],
                            ]
                        ]
                ];
                }
            }
            else{
                $method = 'sendMessage';
                $send_data = ['text' => "Неверный формат трек-номера. Введите корректный трек-номер в формате ABC12345678"];
            }
        }
        else if($checkStateUser["name_comand"] == "getTracingCostumerNumber"){
            $json = $lpost->getOrderInfo(...['CustomerNumber' => $message]);
            $shablon = $lpost->parseJsonTrackingOrder($data,$json,$message);
            $method = 'sendMessage';
            if (empty($shablon["success"])){
                $send_data = ['text' => \implode('', $shablon["shablon"])];
            }
            else{
                $send_data = [
                    'text' => \implode('', $shablon["shablon"]),
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отслеживание заказа']
                            ],
                            [
                                ['text' => 'Информация о заказе']
                            ],
                            [
                                ['text' => 'Контактная информация']
                            ],
                        ]
                    ]
            ];
            // $send_data = ['text' => $shablon];
            }

        }
        else if($checkStateUser["name_comand"] == "getTracingCostumerNumberPlusCustomerPhone"){
            $select_query = "SELECT tracking_number FROM `users_orders` where `chat_id`=:chat order by `date_last_appeal` desc limit 1";
            $params = [
                ':user' => $data["chat"]["id"]
            ];
            $tracking_number = $db->getRow($select_query,$params);  
            $tracking_number = $tracking_number[0]["tracking_number"];
            $json = $lpost->getOrderInfo(...['CustomerPhone' => $message, 'CustomerNumber' => $tracking_number]);
            $shablon = $lpost->parseJsonTrackingOrder($data,$json,$message);
            $method = 'sendMessage';
            if (empty($shablon["success"])){
                $send_data = ['text' => \implode('', $shablon["shablon"])];
            }
            else{
                $send_data = [
                    'text' => \implode('', $shablon["shablon"]),
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отслеживание заказа']
                            ],
                            [
                                ['text' => 'Информация о заказе']
                            ],
                            [
                                ['text' => 'Контактная информация']
                            ],
                        ]
                    ]
            ];
            }
        }
    // ----------------------------------------------------------------------------------------------------------------
        else if($checkStateUser["name_comand"] == "getInfoDeliveryOrderNumber"){
            $message = mb_strtoupper(($data['text'] ?? $data['data']),'utf-8');
            if(preg_match ($regular, $message)){
                $message = preg_replace($regular_only_obrezka, '', $message);
                $json = $lpost->getOrderInfo(...['OrderNumber' => $message]);
                $shablon = $lpost->parseJsonInfoDeliveryOrder($data,$json,$message);
                $method = 'sendMessage';
                if (empty($shablon["success"])){
                    $send_data = ['text' => \implode('', $shablon["shablon"])];
                }
                else{
                    $send_data = [
                        'text' => \implode('', $shablon["shablon"]),
                        'reply_markup' => [
                            'resize_keyboard' => true,
                            'keyboard' => [ 
                                [
                                    ['text' => 'Отслеживание заказа']
                                ],
                                [
                                    ['text' => 'Информация о заказе']
                                ],
                                [
                                    ['text' => 'Контактная информация']
                                ],
                            ]
                        ]
                ];
                }
            }
            else{
                $method = 'sendMessage';
                $send_data = ['text' => "Неверный формат трек-номера. Введите корректный трек-номер в формате ABC12345678"];
            }
        }
        else if($checkStateUser["name_comand"] == "getInfoDeliveryCostumerNumber"){
            $json = $lpost->getOrderInfo(...['CustomerNumber' => $message]);
            $shablon = $lpost->parseJsonInfoDeliveryOrder($data,$json,$message);
            $method = 'sendMessage';
            if (empty($shablon["success"])){
                $send_data = ['text' => \implode('', $shablon["shablon"])];
            }
            else{
                $send_data = [
                    'text' => \implode('', $shablon["shablon"]),
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отслеживание заказа']
                            ],
                            [
                                ['text' => 'Информация о заказе']
                            ],
                            [
                                ['text' => 'Контактная информация']
                            ],
                        ]
                    ]
            ];
            }
        }
        else if($checkStateUser["name_comand"] == "getInfoDeliveryCostumerNumberPlusCustomerPhone"){
            $select_query = "SELECT tracking_number FROM `users_orders` where `chat_id`=:chat order by `date_last_appeal` desc limit 1";
            $params = [
                ':chat' => $data["chat"]["id"]
            ];
            $tracking_number = $db->getRow($select_query,$params);  
            $tracking_number = $tracking_number[0]["tracking_number"];
            $json = $lpost->getOrderInfo(...['CustomerPhone' => $message, 'CustomerNumber' => $tracking_number]);
            $shablon = $lpost->parseJsonInfoDeliveryOrder($data,$json,$message);
            $method = 'sendMessage';
            if (empty($shablon["success"])){
                $send_data = ['text' => \implode('', $shablon["shablon"])];
            }
            else{
                $send_data = [
                    'text' => \implode('', $shablon["shablon"]),
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отслеживание заказа']
                            ],
                            [
                                ['text' => 'Информация о заказе']
                            ],
                            [
                                ['text' => 'Контактная информация']
                            ],
                        ]
                    ]
            ];
            }
        }
    // ----------------------------------------------------------------------------------------------------------------
        else if($checkStateUser["name_comand"] == "getInfoTracingNumberOnPhone"){
            $json = $lpost->getOrderInfo(...['CustomerPhone' => $message]);
            $shablon = $lpost->parseJsonGetInfoTracingNumberOnPhone($json);
            $method = 'sendMessage';
            if (empty($shablon["success"])){
                $send_data = ['text' => \implode('', $shablon["shablon"])];
            }
            else{
                $send_data = [
                    'text' => \implode('', $shablon["shablon"]),
                    'reply_markup' => [
                        'resize_keyboard' => true,
                        'keyboard' => [ 
                            [
                                ['text' => 'Отслеживание заказа']
                            ],
                            [
                                ['text' => 'Информация о заказе']
                            ],
                            [
                                ['text' => 'Контактная информация']
                            ],
                        ]
                    ]
            ];
            }
        }

}

# Добавляем данные пользователя
$send_data['chat_id'] = $data['chat']['id'];
$res = new Telegramm();
$res = $res ->sendTelegram($method, $send_data);

$auth = new LPost();
$t = $auth ->getToken();

?>
 
 
 
 
 
 