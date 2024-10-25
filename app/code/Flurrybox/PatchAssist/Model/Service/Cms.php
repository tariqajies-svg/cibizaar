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

use Exception;
use Flurrybox\PatchAssist\Model\ServiceInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Service for cms actions
 *
 * @api
 */
class Cms implements ServiceInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Page
     */
    protected $pageResource;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Block
     */
    protected $blockResource;

    /**
     * Cms constructor.
     *
     * @param PageFactory $pageFactory
     * @param Page $pageResource
     * @param BlockFactory $blockFactory
     * @param Block $blockResource
     */
    public function __construct(
        PageFactory $pageFactory,
        Page $pageResource,
        BlockFactory $blockFactory,
        Block $blockResource
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
    }

    /**
     * Update or create new cms page
     *
     * @param string $identifier
     * @param string|null $content
     * @param string|null $title
     * @param array $store
     *
     * @return Cms
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function page(
        string $identifier,
        ?string $content = null,
        ?string $title = null,
        array $store = [0, [0]]
    ): Cms {
        [$store, $storeIds] = $store;
        $page = $this->getPage($identifier, $store);

        $page->setStoreId($storeIds);

        if ($title) {
            $page->setTitle($title);
        }

        if ($content) {
            $page->setContent($content);
        }

        $this->pageResource->save($page);

        return $this;
    }

    /**
     * Update or create cms page by passed data
     *
     * @param array $data
     *
     * @return Cms
     * @throws LocalizedException
     */
    public function pageByData(array $data): Cms
    {
        if (!isset($data['identifier'])) {
            throw new LocalizedException(new Phrase('Identifier is required'));
        }

        $store = 0;

        if (isset($data['store'])) {
            $store = $data['store'];
            unset($data['store']);
        }

        $page = $this->getPage($data['identifier'], $store);
        $page->addData($data);

        $this->pageResource->save($page);

        return $this;
    }

    /**
     * Execute custom callable
     *
     * Callable will receive page model and resource, if identifier is passed page will be loaded
     *
     * @param callable $callable
     * @param string|null $identifier
     * @param int $store
     *
     * @return Cms
     */
    public function customPage(callable $callable, ?string $identifier = null, int $store = 0): Cms
    {
        $page = $identifier ? $this->getPage($identifier, $store) : $this->pageFactory->create();

        $callable($page, $this->pageResource);

        return $this;
    }

    /**
     * Load page model by identifier
     *
     * @param string $identifier
     * @param int $store
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getPage(string $identifier, int $store = 0): \Magento\Cms\Model\Page
    {
        $page = $this->pageFactory->create();
        $page->setStoreId($store);

        $this->pageResource->load($page, $identifier, 'identifier');

        if (!$page->getIdentifier()) {
            $page->setIdentifier($identifier);
        }

        return $page;
    }

    /**
     * Delete page by identifier
     *
     * @param string $identifier
     * @param int $store
     *
     * @return Cms
     * @throws Exception
     */
    public function deletePage(string $identifier, int $store = 0): Cms
    {
        $page = $this->getPage($identifier, $store);

        $this->pageResource->delete($page);

        return $this;
    }

    /**
     * Update or create cms block
     *
     * @param string $identifier
     * @param string|null $content
     * @param string|null $title
     * @param array $store
     *
     * @return Cms
     * @throws Exception
     */
    public function block(
        string $identifier,
        ?string $content = null,
        ?string $title = null,
        array $store = [0, [0]]
    ): Cms {
        [$store, $storeIds] = $store;
        $block = $this->getBlock($identifier, $store);

        $block->setStoreId($storeIds);

        if ($title) {
            $block->setTitle($title);
        }

        if ($content) {
            $block->setContent($content);
        }

        $this->blockResource->save($block);

        return $this;
    }

    /**
     * Update or create cms block by passed data
     *
     * @param array $data
     *
     * @return Cms
     * @throws LocalizedException
     * @throws Exception
     */
    public function blockByData(array $data): Cms
    {
        if (!isset($data['identifier'])) {
            throw new LocalizedException(new Phrase('Identifier is required'));
        }

        $store = 0;

        if (isset($data['store'])) {
            $store = $data['store'];
            unset($data['store']);
        }

        $block = $this->getBlock($data['identifier'], $store);
        $block->addData($data);

        $this->blockResource->save($block);

        return $this;
    }

    /**
     * Execute custom callable
     *
     * Callable will receive block model and resource, if identifier is passed block will be loaded
     *
     * @param callable $callable
     * @param string|null $identifier
     * @param int $store
     *
     * @return Cms
     */
    public function customBlock(callable $callable, ?string $identifier = null, int $store = 0): Cms
    {
        $block = $identifier ? $this->getBlock($identifier, $store) : $this->blockFactory->create();

        $callable($block, $this->blockResource);

        return $this;
    }

    /**
     * Load block model by identifier
     *
     * @param string $identifier
     * @param int $store
     *
     * @return \Magento\Cms\Model\Block
     */
    public function getBlock(string $identifier, int $store = 0): \Magento\Cms\Model\Block
    {
        $block = $this->blockFactory->create();
        $block->setStoreId($store);

        $this->blockResource->load($block, $identifier, 'identifier');

        if (!$block->getIdentifier()) {
            $block->setIdentifier($identifier);
        }

        return $block;
    }

    /**
     * Delete block by identifier
     *
     * @param string $identifier
     * @param int $store
     *
     * @return Cms
     * @throws Exception
     */
    public function deleteBlock(string $identifier, int $store = 0): Cms
    {
        $block = $this->getBlock($identifier, $store);

        $this->blockResource->delete($block);

        return $this;
    }
}
