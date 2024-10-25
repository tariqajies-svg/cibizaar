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

namespace Flurrybox\PatchAssist\Code\Generator;

use Flurrybox\PatchAssist\Model\ServiceRegistry;
use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;

/**
 * Generator class for patch context interface
 */
class PatchContextInterfaceGenerator extends PatchContextGenerator
{
    /**
     * @var ServiceRegistry
     */
    protected $serviceRegistry;

    /**
     * PatchContextInterfaceGenerator constructor.
     *
     * @param ServiceRegistry $serviceRegistry
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io|null $ioObject
     * @param CodeGeneratorInterface|null $classGenerator
     * @param DefinedClasses|null $definedClasses
     */
    public function __construct(
        ServiceRegistry $serviceRegistry,
        string $sourceClassName = null,
        string $resultClassName = null,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        if (!$classGenerator) {
            $classGenerator = new \Magento\Framework\Code\Generator\InterfaceGenerator();
        }

        parent::__construct(
            $serviceRegistry,
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );

        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * Strict check for result class name
     *
     * @return bool
     */
    protected function _validateData()
    {
        if ($this->_getResultClassName() !== '\\Flurrybox\\PatchAssist\\Api\\PatchContextInterface') {
            return false;
        }

        return parent::_validateData();
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode(): string
    {
        $this->_classGenerator
            ->setName($this->_getResultClassName())
            ->addProperties($this->_getClassProperties())
            ->addMethods($this->_getClassMethods())
            ->setClassDocBlock($this->_getClassDocBlock());

        return $this->_getGeneratedCode();
    }
}
