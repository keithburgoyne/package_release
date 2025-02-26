<?php

namespace Silverorange\PackageRelease\Config;

use Noodlehaus\AbstractConfig;
use Noodlehaus\Config;
use Noodlehaus\Exception\FileNotFoundException;
use Silverorange\PackageRelease\Git\Manager;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class ReleaseMetadata extends Config
{
    /**
     * @var Silverorange\PackageRelease\Git\Manager
     */
    protected $manager = null;

    public function __construct(Manager $manager, $path)
    {
        $this->setManager($manager);

        try {
            parent::__construct($path);
        } catch (FileNotFoundException $e) {
            $this->data = [];
            AbstractConfig::__construct($this->data);
        }
    }

    public function setManager(Manager $manager): self
    {
        $this->manager = $manager;
        return $this;
    }

    public function get($key, $default = null)
    {
        $value = parent::get($key, $default);

        $value = preg_replace_callback(
            '/\$[A-Za-z_][A-Za-z0-9_]*/u',
            function (array $matches) {
                switch ($matches[0]) {
                    case '$branch':
                        return $this->manager->getCurrentBranch();
                    case '$hostname':
                        return gethostname();
                    default:
                        return $matches[0];
                }
            },
            $value
        );

        return $value;
    }

    protected function getDefaults()
    {
        return [
            'site' => [
                'title' => '',
            ],
            'testing' => [
                'url' => '',
                'command' => '',
            ],
        ];
    }
}
