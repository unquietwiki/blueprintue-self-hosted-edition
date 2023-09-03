<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintPOSTAddCommentTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user
        $sql = <<<SQL
            REPLACE INTO users (`id`, `username`, `slug`, `grade`, `created_at`)
            VALUES (65, 'user_65', 'user_65', 'member', utc_timestamp()),
                   (66, 'user_66', 'user_66', 'member', utc_timestamp())
        SQL;
        static::$db->exec($sql);

        // user infos
        $sql = <<<SQL
            REPLACE INTO users_infos (id_user, count_public_comment, count_private_comment)
            VALUES (65, 1, 3),
                   (66, 0, 0)
        SQL;
        static::$db->exec($sql);

        // blueprints
        $sql = <<<SQL
            REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure)
            VALUES (966, 65, 'slug_public',   'a1', 'my title 1', 1, utc_timestamp(), utc_timestamp(), 'public'),
                   (967, 65, 'slug_unlisted', 'a2', 'my title 2', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                   (968, 65, 'slug_private',  'a3', 'my title 3', 1, utc_timestamp(), utc_timestamp(), 'private')
        SQL;
        static::$db->exec($sql);

        // blueprints version
        $sql = <<<SQL
            REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at)
            VALUES (966, 1, 'First commit', utc_timestamp(), utc_timestamp()),
                   (967, 1, 'First commit', utc_timestamp(), utc_timestamp()),
                   (968, 1, 'First commit', utc_timestamp(), utc_timestamp())
        SQL;
        static::$db->exec($sql);
    }

    /**
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        static::$db->truncateTables('comments');
        // comment
        $sql = <<<SQL
            REPLACE INTO comments (id, id_author, id_blueprint, content, created_at)
            VALUES (10, 65, 966, 'com 1 public', utc_timestamp()),
                   (11, 65, 967, 'com 1 unlisted', utc_timestamp()),
                   (12, 65, 968, 'com 1 private', utc_timestamp())
        SQL;
        static::$db->exec($sql);

        // user infos
        $sql = <<<SQL
            REPLACE INTO users_infos (id_user, count_public_comment, count_private_comment)
            VALUES (65, 1, 3),
                   (66, 0, 0)
        SQL;
        static::$db->exec($sql);

        // blueprints
        $sql = <<<SQL
            REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, comments_count, comments_closed, comments_hidden)
            VALUES (966, 65, 'slug_public',   'a1', 'my title 1', 1, utc_timestamp(), utc_timestamp(), 'public', 1, 0, 0),
                   (967, 65, 'slug_unlisted', 'a2', 'my title 2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 1, 0, 0),
                   (968, 65, 'slug_private',  'a3', 'my title 3', 1, utc_timestamp(), utc_timestamp(), 'private', 1, 0, 0)
        SQL;
        static::$db->exec($sql);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing edit comment process.
     *
     * @return array[]
     */
    public function dataCasesBlueprintPOST_AddComment(): array
    {
        return [
            'add comment OK - public blueprint' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">Your comment has been added</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'add comment OK - unlisted blueprint' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_unlisted',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">Your comment has been added</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'add comment OK - private blueprint' => [
                'sql_queries'          => [],
                'slug'                 => 'slug_private',
                'user_id'              => 65,
                'params'               => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">Your comment has been added</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'add comment KO - comments close' => [
                'sql_queries'           => [
                    'UPDATE blueprints SET comments_closed = 1 WHERE id = 966'
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'add comment KO - comments hidden' => [
                'sql_queries'           => [
                    'UPDATE blueprints SET comments_hidden = 1 WHERE id = 966'
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'csrf incorrect' => [
                'sql_queries'          => [],
                'slug'                 => 'slug_public',
                'user_id'              => 65,
                'params'               => [
                    'form-add_comment-hidden-csrf'      => 'incorrect_csrf',
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no fields' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no csrf' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-textarea-comment' => 'my comment',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no comment' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'empty fields - comment empty' => [
                'sql_queries'           => [],
                'slug'                  => 'slug_public',
                'user_id'               => 65,
                'params'                => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => ' ',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['comment'],
                'fields_has_value'      => ['comment'],
                'fields_label_error'    => [
                    'comment' => 'Comment is required'
                ],
            ],
            'invalid encoding fields - comment' => [
                'sql_queries'          => [],
                'slug'                 => 'slug_public',
                'user_id'              => 65,
                'params'               => [
                    'form-add_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-add_comment-textarea-comment' => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_AddComment
     *
     * @param array      $sqlQueries
     * @param string     $slug
     * @param int|null   $userID
     * @param array|null $params
     * @param bool       $useCsrfFromSession
     * @param bool       $hasRedirection
     * @param bool       $isFormSuccess
     * @param array      $flashMessages
     * @param array      $fieldsHasError
     * @param array      $fieldsHasValue
     * @param array      $fieldsLabelError
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    public function testBlueprintPOSTAddComment(array $sqlQueries, string $slug, ?int $userID, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user in $_SESSION
        $session = ['remove' => [], 'set' => []];
        if ($userID !== null) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-add_comment-hidden-csrf'] = $_SESSION['csrf'];
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // get infos
        $blueprintCommentsCountBefore = (int) static::$db->selectVar('SELECT comments_count FROM blueprints WHERE slug = :slug', ['slug' => $slug]);
        $countersUsersBefore = static::$db->selectAll('SELECT count_public_comment, count_private_comment FROM users_infos ORDER BY id_user ASC');

        // do post action
        $response = $this->getResponseFromApplication('POST', '/blueprint/' . $slug . '/', $params);

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            if ($isFormSuccess) {
                static::assertSame('/blueprint/' . $slug . '/#comment-13', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/blueprint/' . $slug . '/#comments', $response->getHeaderLine('Location'));
            }
            $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['success']['message']);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['error']['message']);
        }

        $blueprintCommentsCountAfter = (int) static::$db->selectVar('SELECT comments_count FROM blueprints WHERE slug = :slug', ['slug' => $slug]);
        $countersUsersAfter = static::$db->selectAll('SELECT count_public_comment, count_private_comment FROM users_infos ORDER BY id_user ASC');
        $commentsAfter = static::$db->selectAll('SELECT * FROM comments');

        if ($isFormSuccess) {
            static::assertCount(4, $commentsAfter);
            static::assertSame($blueprintCommentsCountAfter, $blueprintCommentsCountBefore + 1);
            static::assertEqualsCanonicalizing($countersUsersAfter[1], $countersUsersBefore[1]);

            static::assertSame('13', $commentsAfter[3]['id']);
            static::assertSame('65', $commentsAfter[3]['id_author']);
            if ($slug === 'slug_public') {
                static::assertSame('966', $commentsAfter[3]['id_blueprint']);
            } elseif ($slug === 'slug_unlisted') {
                static::assertSame('967', $commentsAfter[3]['id_blueprint']);
            } elseif ($slug === 'slug_private') {
                static::assertSame('968', $commentsAfter[3]['id_blueprint']);
            }
            static::assertNull($commentsAfter[3]['name_fallback']);
            static::assertSame('my comment', $commentsAfter[3]['content']);

            if ($slug === 'slug_public') {
                static::assertSame((int) $countersUsersAfter[0]['count_public_comment'], (int) $countersUsersBefore[0]['count_public_comment'] + 1);
                static::assertSame((int) $countersUsersAfter[0]['count_private_comment'], (int) $countersUsersBefore[0]['count_private_comment'] + 1);
            } else {
                static::assertSame((int) $countersUsersAfter[0]['count_public_comment'], (int) $countersUsersBefore[0]['count_public_comment']);
                static::assertSame((int) $countersUsersAfter[0]['count_private_comment'], (int) $countersUsersBefore[0]['count_private_comment'] + 1);
            }
        } else {
            static::assertCount(3, $commentsAfter);
            static::assertEqualsCanonicalizing($countersUsersAfter, $countersUsersBefore);
            static::assertSame($blueprintCommentsCountAfter, $blueprintCommentsCountBefore);
        }

        if (\count($sqlQueries) > 0) {
            return;
        }

        // test fields HTML
        $fields = ['comment'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'comment') {
                $value = $hasValue ? \trim($params['form-add_comment-textarea-comment']) : '';
                $this->doTestHtmlMain($response, $this->getHTMLFormAddComment($value, $hasError, $labelError));
            }
        }
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
    protected function getHTMLFormAddComment(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);
        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-add_comment-label-comment form-add_comment-label-comment-error" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--error" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-add_comment-textarea-comment" name="form-add_comment-textarea-comment" placeholder="Markdown supported">$v</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-add_comment-textarea-comment" id="form-add_comment-label-comment-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-add_comment-label-comment" aria-required="true" class="form__input form__input--textarea form__input--invisible" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-add_comment-textarea-comment" name="form-add_comment-textarea-comment" placeholder="Markdown supported">$v</textarea>
<span class="form__feedback"></span>
</div>
HTML;
        // phpcs:enable
    }
}
