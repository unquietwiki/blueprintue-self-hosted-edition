<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\Edit;

use app\helpers\Helper;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintEditPOSTEditPropertiesTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws \Rancoud\Crypt\CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 189,
            'username' => 'user_189',
            'hash'     => Crypt::hash('password_user_189'),
            'slug'     => 'user_189',
            'email'    => 'user_189@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public function dataCasesEditProperties(): array
    {
        return [
            'update OK - edit properties - no changes' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - unlisted' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'unlisted',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - public' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'public',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - ue_version 4.21' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.21',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - comment hide' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'hide',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - comment close' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'close',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration null -> add 1h' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1h',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration null -> add 1d' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1d',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration null -> add 1w' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1w',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration not null -> add 1h' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `expiration`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp() + interval 1 hour)",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1h',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration not null -> add 1d' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `expiration`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp() + interval 1 hour)",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1d',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration not null -> add 1w' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `expiration`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp() + interval 1 hour)",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => '1w',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'update OK - edit properties - expiration not null -> remove' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `expiration`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp() + interval 1 hour)",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'remove',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">Properties has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'csrf incorrect' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'incorrect_csrf',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'missing fields - no csrf' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'missing fields - no exposure' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['ue_version', 'exposure', 'comment'],
                'fields_label_error'    => [],
            ],
            'missing fields - no expiration' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'missing fields - no ue_version' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'comment'],
                'fields_label_error'    => [],
            ],
            'missing fields - no comment' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'empty fields - exposure' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => ' ',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on exposure</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'exposure' => 'Exposure is invalid'
                ],
            ],
            'empty fields - expiration' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => ' ',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on expiration</div>'
                    ]
                ],
                'fields_has_error'      => ['expiration'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'expiration' => 'Expiration is invalid'
                ],
            ],
            'empty fields - ue_version' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => ' ',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on UE version</div>'
                    ]
                ],
                'fields_has_error'      => ['ue_version'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'ue_version' => 'UE version is invalid'
                ],
            ],
            'empty fields - comment' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => ' ',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on comment section</div>'
                    ]
                ],
                'fields_has_error'      => ['comment'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'comment' => 'Comment section is invalid'
                ],
            ],
            'invalid fields - exposure invalid' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'invalid',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on exposure</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'exposure' => 'Exposure is invalid'
                ],
            ],
            'invalid fields - expiration invalid' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'invalid',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on expiration</div>'
                    ]
                ],
                'fields_has_error'      => ['expiration'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'expiration' => 'Expiration is invalid'
                ],
            ],
            'invalid fields - expiration invalid - remove sent but no expiration set' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'remove',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on expiration</div>'
                    ]
                ],
                'fields_has_error'      => ['expiration'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'expiration' => 'Expiration is invalid'
                ],
            ],
            'invalid fields - ue_version invalid' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => 'invalid',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on UE version</div>'
                    ]
                ],
                'fields_has_error'      => ['ue_version'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'ue_version' => 'UE version is invalid'
                ],
            ],
            'invalid fields - comment invalid' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'               => 189,
                'params'                => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'invalid',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">Error(s) on comment section</div>'
                    ]
                ],
                'fields_has_error'      => ['comment'],
                'fields_has_value'      => ['exposure', 'expiration', 'ue_version', 'comment'],
                'fields_label_error'    => [
                    'comment' => 'Comment section is invalid'
                ],
            ],
            'invalid encoding fields - exposure' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => \chr(99999999),
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - expiration' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => \chr(99999999),
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - ue_version' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => \chr(99999999),
                    'form-edit_properties-select-comment'    => 'open',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - comment' => [
                'sql_queries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_properties-hidden-csrf'       => 'csrf_is_replaced',
                    'form-edit_properties-select-exposure'   => 'private',
                    'form-edit_properties-select-expiration' => 'keep',
                    'form-edit_properties-select-ue_version' => '4.0',
                    'form-edit_properties-select-comment'    => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_properties">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['exposure', 'ue_version', 'comment'],
                'fields_label_error'    => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesEditProperties
     *
     * @param array $sqlQueries
     * @param int   $userID
     * @param array $params
     * @param bool  $useCsrfFromSession
     * @param bool  $hasRedirection
     * @param bool  $isFormSuccess
     * @param array $flashMessages
     * @param array $fieldsHasError
     * @param array $fieldsHasValue
     * @param array $fieldsLabelError
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     * @throws \Exception
     */
    public function testBlueprintEditPOSTEditProperties(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        static::setDatabase();

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => ['userID' => $userID],
            'remove' => []
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-edit_properties-hidden-csrf'] = $_SESSION['csrf'];
        }

        // database before
        $blueprintBefore = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/blueprint/slug_1/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/blueprint/slug_1/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // database after
        $blueprintAfter = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');

        if ($isFormSuccess) {
            static::assertSame($params['form-edit_properties-select-exposure'], $blueprintAfter['exposure']);
            static::assertSame($params['form-edit_properties-select-ue_version'], $blueprintAfter['ue_version']);

            // expiration
            if ($params['form-edit_properties-select-expiration'] === 'remove') {
                static::assertNull($blueprintAfter['expiration']);
            } elseif ($params['form-edit_properties-select-expiration'] === 'keep') {
                static::assertSame($blueprintBefore['expiration'], $blueprintAfter['expiration']);
            } else {
                $convert = [
                    '1h' => 'hour',
                    '1d' => 'day',
                    '1w' => 'week'
                ];

                $startDate = $blueprintBefore['expiration'] ?? $blueprintAfter['updated_at'];
                $date = (new DateTime($startDate, new DateTimeZone('UTC')));
                $date->modify('+1 ' . $convert[$params['form-edit_properties-select-expiration']]);

                static::assertSame($date->format('Y-m-d H:i:s'), $blueprintAfter['expiration']);
            }

            // comment
            if ($params['form-edit_properties-select-comment'] === 'open') {
                static::assertSame(0, (int) $blueprintAfter['comments_hidden']);
                static::assertSame(0, (int) $blueprintAfter['comments_closed']);
            } elseif ($params['form-edit_properties-select-comment'] === 'close') {
                static::assertSame(0, (int) $blueprintAfter['comments_hidden']);
                static::assertSame(1, (int) $blueprintAfter['comments_closed']);
            } elseif ($params['form-edit_properties-select-comment'] === 'hidden') {
                static::assertSame(1, (int) $blueprintAfter['comments_hidden']);
                static::assertSame(1, (int) $blueprintAfter['comments_closed']);
            }
        } else {
            static::assertSame($blueprintBefore, $blueprintAfter);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['success']['message']);
        }

        // test fields HTML
        $fields = ['exposure', 'expiration', 'ue_version', 'comment'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'exposure') {
                if (isset($params['form-edit_properties-select-exposure']) && $params['form-edit_properties-select-exposure'] !== \chr(99999999)) {
                    $value = $hasValue ? \trim($params['form-edit_properties-select-exposure']) : '';
                } else {
                    $value = 'private';
                }
                $this->doTestHtmlForm($response, '#form-edit_properties', $this->getHTMLFieldExposure($value, $hasError, $labelError));
            }

            if ($field === 'expiration') {
                if ($isFormSuccess) {
                    $value = '';
                } elseif (isset($params['form-edit_properties-select-expiration']) && $params['form-edit_properties-select-expiration'] !== \chr(99999999)) {
                    $value = $hasValue ? \trim($params['form-edit_properties-select-expiration']) : '';
                } else {
                    $value = '';
                }
                $this->doTestHtmlForm($response, '#form-edit_properties', $this->getHTMLFieldExpiration($value, $hasError, $labelError, $blueprintAfter['expiration']));
            }

            if ($field === 'ue_version') {
                if (isset($params['form-edit_properties-select-ue_version']) && $params['form-edit_properties-select-ue_version'] !== \chr(99999999)) {
                    $value = $hasValue ? \trim($params['form-edit_properties-select-ue_version']) : '';
                } else {
                    $value = '4.0';
                }
                $this->doTestHtmlForm($response, '#form-edit_properties', $this->getHTMLFieldUEVersion($value, $hasError, $labelError));
            }

            if ($field === 'comment') {
                if (isset($params['form-edit_properties-select-comment']) && $params['form-edit_properties-select-comment'] !== \chr(99999999)) {
                    $value = $hasValue ? \trim($params['form-edit_properties-select-comment']) : '';
                } else {
                    $value = 'open';
                }
                $this->doTestHtmlForm($response, '#form-edit_properties', $this->getHTMLFieldComment($value, $hasError, $labelError));
            }
        }
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @return string
     */
    protected function getHTMLFieldExposure(string $value, bool $hasError, string $labelError): string
    {
        $publicSelected = ($value === 'public') ? ' selected="selected"' : '';
        $unlistedSelected = ($value === 'unlisted') ? ' selected="selected"' : '';
        $privateSelected = ($value === 'private') ? ' selected="selected"' : '';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-exposure" id="form-edit_properties-label-exposure">Exposure</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-exposure form-edit_properties-label-exposure-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-edit_properties-select-exposure" name="form-edit_properties-select-exposure">
<option value="public"$publicSelected>Public</option>
<option value="unlisted"$unlistedSelected>Unlisted</option>
<option value="private"$privateSelected>Private</option>
</select>
</div>
<label class="form__label form__label--error" for="form-edit_properties-select-exposure" id="form-edit_properties-label-exposure-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-exposure" id="form-edit_properties-label-exposure">Exposure</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-exposure" aria-required="true" class="form__input form__input--select" id="form-edit_properties-select-exposure" name="form-edit_properties-select-exposure">
<option value="public"$publicSelected>Public</option>
<option value="unlisted"$unlistedSelected>Unlisted</option>
<option value="private"$privateSelected>Private</option>
</select>
</div>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string      $value
     * @param bool        $hasError
     * @param string      $labelError
     * @param string|null $expirationDate
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldExpiration(string $value, bool $hasError, string $labelError, ?string $expirationDate): string // phpcs:ignore
    {
        $keepSelected = ($value === 'keep') ? ' selected="selected"' : '';
        $removeSelected = ($value === 'remove') ? ' selected="selected"' : '';
        $oneHSelected = ($value === '1h') ? ' selected="selected"' : '';
        $oneDSelected = ($value === '1d') ? ' selected="selected"' : '';
        $oneWSelected = ($value === '1w') ? ' selected="selected"' : '';

        // phpcs:disable
        if ($expirationDate === null) {
            $expirationHelp = '';
            $expirationText = '</div>';
            $options = <<<HTML
<option value="keep"$keepSelected>No expiration</option>
<option value="1h"$oneHSelected>Set expiration to 1 hour</option>
<option value="1d"$oneDSelected>Set expiration to 1 day</option>
<option value="1w"$oneWSelected>Set expiration to 1 week</option>
HTML;
        } else {
            $expirationHelp = 'aria-describedby="form-edit_properties-span-help" ';
            $expirationText = '<span class="form__help" id="form-edit_properties-span-help">Blueprint expired at <span class="form__help--emphasis">' . Security::escHTML($expirationDate) . '</span></span>' . "\n" . '</div>';
            $options = <<<HTML
<option value="keep"$keepSelected>Keep expiration time</option>
<option value="1h"$oneHSelected>Add 1 hour</option>
<option value="1d"$oneDSelected>Add 1 day</option>
<option value="1w"$oneWSelected>Add 1 week</option>
<option value="remove"$removeSelected>Remove expiration time</option>
HTML;
        }

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-expiration" id="form-edit_properties-label-expiration">Expiration</label>
<div class="form__container form__container--select">
<select {$expirationHelp}aria-invalid="false" aria-labelledby="form-edit_properties-label-expiration form-edit_properties-label-expiration-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-edit_properties-select-expiration" name="form-edit_properties-select-expiration">
$options
</select>
</div>
<label class="form__label form__label--error" for="form-edit_properties-select-expiration" id="form-edit_properties-label-expiration-error">$labelError</label>
$expirationText
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-expiration" id="form-edit_properties-label-expiration">Expiration</label>
<div class="form__container form__container--select">
<select {$expirationHelp}aria-invalid="false" aria-labelledby="form-edit_properties-label-expiration" aria-required="true" class="form__input form__input--select" id="form-edit_properties-select-expiration" name="form-edit_properties-select-expiration">
$options
</select>
</div>
$expirationText
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldUEVersion(string $value, bool $hasError, string $labelError): string
    {
        $listOptions = [];
        foreach (Helper::getAllUEVersion() as $ueVersion) {
            // phpcs:disable
            $listOptions[] = '<option value="' . Security::escAttr($ueVersion) . '"' . (($value === $ueVersion) ? ' selected="selected"' : '') . '>' . Security::escHTML($ueVersion) . '</option>';
            // phpcs:enable
        }
        $listOptionsStr = \implode("\n", $listOptions);

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-ue_version form-edit_properties-label-ue_version-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-edit_properties-select-ue_version" name="form-edit_properties-select-ue_version">
$listOptionsStr
</select>
</div>
<label class="form__label form__label--error" for="form-edit_properties-select-ue_version" id="form-edit_properties-label-ue_version-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-ue_version" aria-required="true" class="form__input form__input--select" id="form-edit_properties-select-ue_version" name="form-edit_properties-select-ue_version">
$listOptionsStr
</select>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @return string
     */
    protected function getHTMLFieldComment(string $value, bool $hasError, string $labelError): string
    {
        $openSelected = ($value === 'open') ? ' selected="selected"' : '';
        $closeSelected = ($value === 'close') ? ' selected="selected"' : '';
        $hideSelected = ($value === 'hide') ? ' selected="selected"' : '';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-comment" id="form-edit_properties-label-comment">Comment sections</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-comment form-edit_properties-label-comment-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-edit_properties-select-comment" name="form-edit_properties-select-comment">
<option value="open"$openSelected>Open - All members can comment and see other comments</option>
<option value="close"$closeSelected>Close - No one can comment but the comments are still visible</option>
<option value="hide"$hideSelected>Hide - No one can comment and the comments are hidden</option>
</select>
</div>
<label class="form__label form__label--error" for="form-edit_properties-select-comment" id="form-edit_properties-label-comment-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_properties-select-comment" id="form-edit_properties-label-comment">Comment sections</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-edit_properties-label-comment" aria-required="true" class="form__input form__input--select" id="form-edit_properties-select-comment" name="form-edit_properties-select-comment">
<option value="open"$openSelected>Open - All members can comment and see other comments</option>
<option value="close"$closeSelected>Close - No one can comment but the comments are still visible</option>
<option value="hide"$hideSelected>Hide - No one can comment and the comments are hidden</option>
</select>
</div>
</div>
HTML;
        // phpcs:enable
    }
}
