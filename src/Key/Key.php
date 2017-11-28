<?php

namespace Bolt\Extension\Bolt\PreviewLink\Key;

use Silex\Application;

/**
 * Class to handle hash key creation and verification.
 *
 * @author Néstor de Dios Fernández <nestor@twokings.nl>
 */
class Key
{

    private $app;
    private $key;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getKey($contenttypeslug, $id)
    {
        $config = $this->app['previewlink.config'];
        $salt = $config['salt'];
        $key = password_hash($contenttypeslug . $id . $salt, PASSWORD_BCRYPT);

        return $key;
    }

    public function checkKey($contenttypeslug, $id, $hash)
    {
        $config = $this->app['previewlink.config'];
        $salt = $config['salt'];

        return password_verify($contenttypeslug . $id . $salt, $hash);
    }
}
