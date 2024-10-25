<?php
/**
 * This file is part of the Flurrybox PatchAssist package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Flurrybox PatchAssist
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Flurrybox, Ltd. (https://flurrybox.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Flurrybox\PatchAssist\Model\Service;

use Flurrybox\PatchAssist\Model\ServiceInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Service for retrieving setup template files
 *
 * @api
 */
class Template implements ServiceInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Template constructor.
     *
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(Reader $reader, Filesystem $filesystem, DataObjectFactory $dataObjectFactory)
    {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get setup template from specific module
     *
     * @param string $module
     * @param string $template
     * @param array $vars
     *
     * @return string|null
     */
    public function get(string $module, string $template, array $vars = []): ?string
    {
        $file = implode(DIRECTORY_SEPARATOR, [
            $this->reader->getModuleDir(Dir::MODULE_SETUP_DIR, $module),
            'Patch',
            'template',
            $template . '.phtml'
        ]);

        if (!$this->filesystem->exists($file)) {
            return null;
        }

        ob_start();

        $block = $this->dataObjectFactory->create(['data' => $vars]);
        require $file;

        return ob_get_clean();
    }
}
