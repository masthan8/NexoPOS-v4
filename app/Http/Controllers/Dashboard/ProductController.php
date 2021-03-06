<?php
/**
 * NexoPOS Controller
 * @since  1.0
**/

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Exception;
use Tendoo\Core\Exceptions\NotFoundException;

class ProductController extends Controller
{
    /** @var ProductService */
    protected $productService;

    public function __construct( 
        ProductService $productService
    )
    {
        parent::__construct();

        $this->productService   =   $productService;
    }

    public function saveProduct( Request $request )
    {
        /**
         * prior field validation
         */
        $fields     =   $request->only([ 
            'name', 
            'tax_type', 
            'sale_price', 
            'product_type', // simple, extended
            'type', 
            'gross_sale_price', 
            'status', 
            'stock_management', 
            'on_expiration', 
            'barcode', 
            'barcode_type', 
            'sku', 
            'description', 
            'thumbnail_id', 
            'category_id', 
            'tax_id', 
            'selling_unit_id', 
            'selling_unit_type',
            'purchase_unit_id', 
            'purchase_unit_type',
            'transfer_unit_id', 
            'transfer_unit_type',
            'expiration',
            'variations'
        ]);

        /**
         * the method "create" is capable of 
         * creating either a product or a variable product
         */
        return $this->productService->create( $fields );
    }

    /**
     * returns a list of available 
     * product
     * @return array
     */
    public function getProduts()
    {
        return $this->productService->getProducts();
    }

    /**
     * Update a product using
     * a provided id
     * @param Request
     * @param int product id
     * @return array
     */
    public function updateProduct( Request $request, $identifier )
    {
        /**
         * prior field validation
         */
        $fields     =   $request->only([ 
            'name', 
            'tax_type', 
            'sale_price', 
            'product_type', // product, variation, variable
            'type', 
            'gross_sale_price', 
            'status', 
            'stock_management', 
            'on_expiration', 
            'barcode', 
            'barcode_type', 
            'sku', 
            'description', 
            'thumbnail_id', 
            'category_id', 
            'tax_id', 
            'selling_unit_id', 
            'selling_unit_type',
            'purchase_unit_id', 
            'purchase_unit_type',
            'transfer_unit_id', 
            'transfer_unit_type',
            'variations',
            'expiration' 
        ]);

        $product    =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );

        /**
         * let's handle single product
         * for the meantime
         */
        return $this->productService->update( $product, $fields );
    }

    public function refreshPrices( $id )
    {
        $product    =   $this->productService->get( $id );
        $this->productService->refreshPrices( $product );
        
        return [
            'status'    =>  'success',
            'message'   =>  __( 'The product price has been refreshed.' ),
            'data'      =>  compact( 'product' )
        ];
    }

    public function reset( $identifier )
    {
        $product        =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );
        
        return $this->productService->resetProduct( $product );
    }

    /**
     * return the full history of a product
     * @param int product id
     * @return array
     */
    public function history( $identifier )
    {
        $product        =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );

        return $this->productService->getProductHistory( 
            $product->id
        );
    }

    public function units( $identifier )
    {
        $product        =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );
        
        return $this->productService->getUnitQuantities( 
            $product->id
        );
    }

    /**
     * delete a product
     * @param int product_id
     * @return array reponse
     */
    public function deleteProduct( $identifier )
    {
        $product        =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );

        return $this->productService->deleteProduct( $product );
    }

    /**
     * Return a single product ig that exists
     * with his variations
     * @param string|int filter
     * @return array found product
     */
    public function singleProduct( $identifier )
    {
        return $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );
    }

    /**
     * return all available variations
     * @return array
     */
    public function getAllVariations()
    {
        return $this->productService->getProductVariations();
    }

    /**
     * delete all available product variations
     */
    public function deleteAllVariations()
    {
        return $this->productService->deleteVariations();
    }

    public function deleteAllProducts()
    {
        return $this->productService->deleteAllProducts();        
    }

    public function getProductVariations( $identifier )
    {
        $product    =   $this->productService->getProductUsingArgument(
            request()->query( 'as' ) ?? 'id',
            $identifier
        );

        return $product->variations;
    }

    /**
     * delete a single variation product
     * @param int product id
     * @param int variation id
     * @return array status of the operation
     */
    public function deleteSingleVariation( $product_id, int $variation_id )
    {
        /**
         * @todo consider registering an event for 
         * catching when a single is about to be delete
         */

        /** @var Product */
        $product    =   $this->singleProduct( $product_id );

        $results    =   $product->variations->map( function( $variation ) use ( $variation_id ) {
            if ( $variation->id === $variation_id ) {
                $variation->delete();
                return 1;
            }
            return 0;
        });

        $opResult   =   $results->reduce( function( $before, $after ) {
            return $before + $after;
        });

        return floatval( $opResult ) > 0 ? [
            'status'        =>      'success',
            'message'       =>      __( 'The single variation has been deleted.' )
        ] : [
            'status'        =>      'failed',
            'message'       =>      sprintf( __( 'The the variation hasn\'t been deleted because it might not exist or is not assigned to the parent product "%s".' ), $product->name )
        ];
    }

    /**
     * Create a single product
     * variation
     * @param int product id (parent)
     * @param Request data
     * @return array
     */
    public function createSingleVariation( $product_id, Request $request )
    {
        $product    =   $this->productService->get( $product_id );
        return $this->productService->createProductVariation( $product, $request->all() );
    }

    public function editSingleVariation( $parent_id, $variation_id, Request $request )
    {
        $parent     =   $this->productService->get( $parent_id );
        return $this->productService->updateProductVariation( $parent, $variation_id, $request->all() );
    }
}

