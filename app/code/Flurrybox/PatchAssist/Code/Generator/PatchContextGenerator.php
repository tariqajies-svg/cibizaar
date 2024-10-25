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
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\DataObject;

/**
 * Generator class for patch context
 */
class PatchContextGenerator extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'patchContext';

    /**
     * @var ServiceRegistry
     */
    protected $serviceRegistry;

    /**
     * PatchContextGenerator constructor.
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
        // As our custom class has no existing source, default to any existing class
        // just for sake of passing validation in generator
        $sourceClassName = DataObject::class;

        parent::__construct($sourceClassName, $resultClassName, $ioObject, $classGenerator, $definedClasses);

        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * Get class documentation block
     *
     * @return array
     */
    protected function _getClassDocBlock(): array
    {
        return [
            'shortDescription' => "Context model with defined patch services\n\n@api"
        ];
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition(): array
    {
        $services = $this->serviceRegistry->getServices();
        $constructor = [
            'name' => '__construct',
            'parameters' => [],
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => []
            ],
            'body' => ''
        ];

        foreach ($services as $key => $service) {
            $constructor['parameters'][] = ['name' => $key, 'type' => '\\' . get_class($service) . '\\Proxy'];
            $constructor['docblock']['tags'][] = [
                'name' => 'param',
                'description' => '\\' . get_class($service) . '\\Proxy $' . $key
            ];
            $constructor['body'] .= '$this->' . $key . ' = $' . $key . ';';

            if (next($services) !== null) {
                $constructor['body'] .= PHP_EOL;
            }
        }

        return $constructor;
    }

    /**
     * Returns list of properties for class generator
     *
     * @return array
     */
    protected function _getClassProperties(): array
    {
        $params = [];

        foreach ($this->serviceRegistry->getServices() as $key => $service) {
            $params[] = [
                'name' => $key,
                'visibility' => 'protected',
                'docblock' => [
                    'tags' => [['name' => 'var', 'description' => '\\' . get_class($service) . '\\Proxy']]
                ]
            ];
        }

        return $params;
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods(): array
    {
        $methods = [];

        foreach ($this->serviceRegistry->getServices() as $key => $service) {
            $methods[] = [
                'name' => $key,
                'parameters' => [],
                'body' => 'return $this->' . $key . ';',
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'return',
                            'description' => '\\' . get_class($service) . '\\Proxy'
                        ]
                    ]
                ],
                'returntype' => '\\' . get_class($service) . '\\Proxy'
            ];
        }

        return $methods;
    }

    /**
     * Strict check for result class name
     *
     * @return bool
     */
    protected function _validateData()
    {
        if (
            !$this->_classGenerator instanceof \Magento\Framework\Code\Generator\InterfaceGenerator &&
            $this->_getResultClassName() !== '\\Flurrybox\\PatchAssist\\Api\\PatchContext'
        ) {
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
        $methods = $this->_getClassMethods();
        array_unshift($methods, $this->_getDefaultConstructorDefinition());

        $this->_classGenerator
            ->setName($this->_getResultClassName())
            ->setImplementedInterfaces([$this->_getResultClassName() . 'Interface'])
            ->addProperties($this->_getClassProperties())
            ->addMethods($methods)
            ->setClassDocBlock($this->_getClassDocBlock());

        return $this->_getGeneratedCode();
    }
}
