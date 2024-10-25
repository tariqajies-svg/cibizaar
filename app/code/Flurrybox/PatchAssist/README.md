# Use case

Service classes used in patch scripts to provide simplified functionality for creating CMS pages and blocks, changing configs, creating entity attributes, and copying media files from both, themes and modules, to project media path.

# Patch context

To use \Flurrybox\PatchAssist\Api\PatchContextInterface that will include all services:

1\. Add DI configuration to primary di.xml

```xml
<type name="Magento\Framework\Code\Generator">
    <arguments>
        <argument name="generatedEntities" xsi:type="array">
            ...
            <item name="patchContext" xsi:type="string">Flurrybox\PatchAssist\Code\Generator\PatchContextGenerator</item>
            <item name="patchContextInterface" xsi:type="string">Flurrybox\PatchAssist\Code\Generator\PatchContextInterfaceGenerator</item>
            ...
        </argument>
    </arguments>
</type>

```
2\. Require interface in patch script

```php
<?php

namespace ...;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class Patch implements DataPatchInterface
{
    public function __construct(
       ...
       \Flurrybox\PatchAssist\Api\PatchContextInterface $patchContext,
       ...
    ) {
       ...
       $this->patchContext = $patchContext;
       ...
    }
    
    ...
}
```
