<?php

/**
 * Laravel Propel integration.
 *
 * @author    Alexander Zhuravlev <scif-1986@ya.ru>
 * @author    Maxim Soloviev <BigShark666@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/propelorm/PropelLaravel
 */

namespace Propel\PropelLaravel\Auth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Propel\Runtime\ActiveQuery\Criteria;

class PropelUserProvider implements UserProviderInterface
{
    /**
     * The active propel query.
     *
     * @var Criteria
     */
    protected $query;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Create a new database user provider.
     *
     * @param Criteria                             $query
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     */
    public function __construct(Criteria $query, HasherContract $hasher)
    {
        $this->query = $query;
        $this->hasher = $hasher;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->query->findPK($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param mixed  $identifier
     * @param string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->query->filterById($identifier)
            ->filterByRememberToken($token)
            ->findOne();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $this->query->filterById($user->getAuthIdentifier())
            ->update(['RememberToken' => $token]);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $query = $this->query;

        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->{"filterBy{$key}"}($value);
            }
        }

        return $query->findOne();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
