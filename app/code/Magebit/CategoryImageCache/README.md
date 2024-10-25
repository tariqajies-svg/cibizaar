# Magebit - Category Image Cache + Resize

This extension adds caching functionality for category images.

Category cache folder is in pub/media/catalog/category/cache.
Structure and generation logic is similar to default product image caching (pub/media/catalog/product/cache)

## Usage

In your .phtml template include view model (example for Hyva theme):

$categoryImageResizerViewModel = $viewModels->require(CategoryImageResizer::class);

Then simply call resize method:

$categoryImageResizerViewModel->resize($category->getImage(), 120, 120)
