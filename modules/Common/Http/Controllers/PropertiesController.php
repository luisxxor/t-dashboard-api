<?php

namespace Modules\Common\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\GoogleStorageHandler;
use App\Lib\Writer\Common\FileWriterFactory;
use App\Repositories\Dashboard\OrderRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Common\Repositories\PropertyRepository;
use Modules\Common\Repositories\PropertyTypeRepository;
use Modules\Common\Repositories\SearchRepository;

/**
 * Class PropertiesController
 * @package Modules\Common\Http\Controllers
 */
class PropertiesController extends AppBaseController
{
    /**
     * @var PropertyTypeRepository
     */
    protected $propertyTypeRepository;

    /**
     * @var PropertyRepository
     */
    protected $propertyRepository;

    /**
     * @var SearchRepository
     */
    protected $searchRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var GoogleStorageHandler
     */
    protected $googleStorageHandler;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PropertyTypeRepository $propertyTypeRepo,
        PropertyRepository $propertyRepo,
        SearchRepository $searchRepo,
        OrderRepository $orderRepo )
    {
        $this->googleStorageHandler = new GoogleStorageHandler();
        $this->propertyTypeRepository = $propertyTypeRepo;
        $this->propertyRepository = $propertyRepo;
        $this->searchRepository = $searchRepo;
        $this->orderRepository = $orderRepo;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProperties( Request $request )
    {
        $request->validate( [
            'vertices'  => [ 'required', 'array', 'filled' ],
            'filters'   => [ 'nullable', 'array' ],
            'lat'       => [ 'required', 'numeric' ],
            'lng'       => [ 'required', 'numeric' ],
            'address'   => [ 'nullable', 'string' ],
            'perpage'   => [ 'required', 'integer', 'min:10', 'max:500' ],
        ] );

        // input
        $vertices   = $request->get( 'vertices' );
        $filters    = $request->get( 'filters' );
        $lat        = $request->get( 'lat' );
        $lng        = $request->get( 'lng' );
        $address    = $request->get( 'address' );
        $perpage    = (int)$request->get( 'perpage' );

        // paginate data (default)
        $field  = 'publication_date';
        $sort   = -1;

        // get user
        $user = auth()->user();

        // metadata data
        $searchData = [
            'user_id' => $user->id,
            'metadata' => [
                'vertices' => $vertices,
                'filters' => (object)$filters,
                'initPoint' => [
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                    'address' => $address,
                ],
            ],
            'created_at' => new DateTime( 'now' )
        ];

        // insert into 'searches' collection
        $search = $this->searchRepository->create( $searchData );
        $searchId = $search->id;

        // construct and execute query.
        // this will return the matched properties.
        $data = $this->propertyRepository->searchPropertiesReturnOutputFields( $search, compact( 'perpage', 'field', 'sort' ) );

        if ( empty( $data ) === true ) {
            return $this->sendError( 'No properties matched.', $data, 204 );
        }

        return $this->sendResponse( compact( 'data', 'searchId' ), 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countSearch( Request $request )
    {
        $request->validate( [
            'searchId'      => [ 'required', 'string' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );

        try {
            // get search model
            $search = $this->searchRepository->findOrFail( $searchId );

            $total = $this->propertyRepository->countSearchedProperties( $search );
        }
        catch ( Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        return $this->sendResponse( $total, 'Search count retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginateSearch( Request $request )
    {
        $request->validate( [
            'searchId'                  => [ 'required', 'string' ],
            'lastItem'                  => [ 'required', 'array', 'filled' ],
            'lastItem._id'              => [ 'required', 'integer', 'filled' ],
            'lastItem.publication_date' => [ 'required', 'date_format:Y-m-d H:i:s', 'filled' ],
            'perpage'                   => [ 'required', 'integer', 'min:1', 'max:500' ],
            'field'                     => [ 'nullable', 'string', Rule::notIn( [ 'distance', '_id' ] ) ],
            'sort'                      => [ 'nullable', 'integer', 'in:1,-1' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $lastItem   = [
            '_id'               => (int)$request->get( 'lastItem' )[ '_id' ],
            'publication_date'  => $request->get( 'lastItem' )[ 'publication_date' ],
        ];
        $perpage    = (int)$request->get( 'perpage' );
        $field      = $request->get( 'field' )      ?? 'publication_date';
        $sort       = $request->get( 'sort' )       ?? -1;

        try {
            // get search model
            $search = $this->searchRepository->findOrFail( $searchId );

            // construct and execute query
            $data = $this->propertyRepository->searchPropertiesReturnOutputFields( $search, compact( 'perpage', 'field', 'sort', 'lastItem' ) );
        }
        catch ( Exception $e ) {
            return $this->sendError( $e->getMessage(), [], 404 );
        }

        if ( empty( $data ) === true ) {
            return $this->sendError( 'No properties matched.', $data, 204 );
        }

        return $this->sendResponse( compact( 'data', 'searchId' ), 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function order( Request $request )
    {
        $request->validate( [
            'searchId'  => [ 'required', 'string' ],
            'ids'       => [ 'required', 'array' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $ids        = $request->get( 'ids' );

        // get user
        $user = auth()->user();

        // check if user has active subscriptions for this project
        if ( $user->hasActiveSubscriptionsForProject( $this->propertyRepository->projectCode() ) === false && $user->hasPermissionTo( 'release.order.without.paying' ) === false ) { # cambiar rol 'release.order.without.paying' por uno nuevo
            return $this->sendError( 'Cannot create order because user has no subscription or is expired.', [], 402 );
        }

        // check if user can make an order for given project
        if ( $user->canOrderBySubscription( $this->propertyRepository->projectCode() ) === false && $user->hasPermissionTo( 'release.order.without.paying' ) === false ) { # cambiar rol 'release.order.without.paying' por uno nuevo
            return $this->sendError( 'User subscription has exhausted the download quota.', [], 409 );
        }

        try {
            // get search model
            $search = $this->searchRepository->findOrFail( $searchId );
        }
        catch ( Exception $e ) {
            return $this->sendError( $e->getMessage(), [], 404 );
        }

        // get selected ids by user
        if ( $ids === [ '*' ] ) {
            $total = $this->propertyRepository->countSearchedProperties( $search ); # esto hace una consulta
        }
        else {
            $total = count( $ids );
        }

        // get order if exist
        $order = $this->orderRepository->findByField( 'search_id', $searchId )->first();

        // if order doesn't exist
        if ( empty( $order ) === true ) {
            // create order
            $order = $this->orderRepository->create( [
                'user_id'               => $user->id,
                'search_id'             => $searchId,
                'project'               => config( 'multi-api.' . $this->propertyRepository->projectCode() . '.backend-info.code' ),
                'total_rows_quantity'   => $total,
                'status'                => config( 'constants.ORDERS.STATUS.OPENED' ),
            ] );

            // record subscription usage
            $user->recordSubscriptionUsage();
        }
        else {
            // if order is already processed
            if ( $order->status !== config( 'constants.ORDERS.STATUS.OPENED' ) ) {
                return $this->sendError( 'The order is already created and has already been processed.', [], 400 );
            }

            // update order
            $order->total_rows_quantity = $total;
            $order->save();
        }

        // update the search to save selected ids by user
        $this->propertyRepository->updateSelectedPropertiesInSearch( $search, $ids );

        // check if user can release order whether by subscription or by permission
        if ( $user->canReleaseOrderBySubscription( $this->propertyRepository->projectCode() ) === true || $user->hasPermissionTo( 'release.order.without.paying' ) === true ) {
            $order = $order->setReleasedStatus();

            return $this->sendResponse( $order, 'Ordered successfully, file generated.', 201 );
        }

        // return order
        return $this->sendResponse( $order, 'Ordered successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePropertiesFile( Request $request )
    {
        $request->validate( [
            'orderCode'  => [ 'required', 'string' ],
        ] );

        // input
        $orderCode = $request->get( 'orderCode' );

        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();

        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', [ $orderCode ] );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        $filesInfo = [];

        try {
            $bucketName = config( 'app.export_file_buckets.' . $this->propertyRepository->projectCode() );
            if ( empty( $bucketName ) === true ) {
                throw new Exception( 'bucket not defined for project: ' . $this->propertyRepository->projectCode() );
            }

            // get search
            $search = $this->searchRepository->findOrFail( $order->search_id );

            // create json metadata file
            $jsonMetadataFile = FileWriterFactory::createWriter( 'json' )
                ->openToFile( $orderCode . '.metadata.json' )
                ->addRow( $this->createJSONRow( $search->toArray() ) );
            $path = $jsonMetadataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( $bucketName, $path, $order->total_rows_quantity );

            // free memory
            unset( $jsonMetadataFile );
            gc_collect_cycles();

            // create json data file
            $ndjsonDataFile = FileWriterFactory::createWriter( 'json' )
                ->openToFile( $orderCode . '.ndjson' );

            // create xlsx data file
            $xlsxDataFile = FileWriterFactory::createWriter( 'xlsx' )
                ->openToFile( $orderCode . '.xlsx' );
            $xlsxDataFile->addRow( $this->propertyRepository->flattenedHeader(), true );

            $perpage = 25;
            $lastItem = [];
            $customId = 1;
            do {
                $selectedSearchedProperties = $this->propertyRepository->getSelectedPropertiesFromProperties( $search, compact( 'perpage', 'lastItem' ) );

                foreach ( $selectedSearchedProperties as $item ) {
                    $item[ 'customId' ] = $customId;

                    // add json data row
                    $ndjsonDataFile->addRow( $this->createJSONRow( $item ) . PHP_EOL );

                    // add xlsx data row
                    $xlsxDataFile->addRow( $this->createXLSXRow( $item ) );

                    $customId++;
                }

                $lastItem = [
                    '_id' => $item[ '_id' ],
                    'publication_date' => $item[ 'publication_date' ],
                ];
            } while ( empty( $selectedSearchedProperties ) === false );

            // close json data file
            $path = $ndjsonDataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( $bucketName, $path, $order->total_rows_quantity );

            // close xslx data file
            $path = $xlsxDataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( $bucketName, $path, $order->total_rows_quantity );

            // free memory
            unset( $search );
            unset( $selectedSearchedProperties );
            unset( $ndjsonDataFile );
            unset( $xlsxDataFile );
            gc_collect_cycles();
        }
        catch ( Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        $order->files_info = $filesInfo;
        $order->save();

        return $this->sendResponse( 'OK', 'Properties\' file generated successfully.' );
    }

    /**
     * Creates a custom format row for json data file.
     *
     * @param array $item The item that needs to be formatted to the row.
     *
     * @return string
     */
    protected function createJSONRow( array $item ): string
    {
        return json_encode( $item, JSON_UNESCAPED_SLASHES );
    }

    /**
     * Creates a custom format row for xlsx data file.
     *
     * @param array $item The item that needs to be formatted to the row.
     *
     * @return array
     */
    protected function createXLSXRow( array $item ): array
    {
        $xlsxFields = collect( $item );

        // flatten nested values
        foreach ( $xlsxFields as $key => $value ) {
            if ( is_object( $value ) === true ) {
                $xlsxFields = $xlsxFields->merge( collect( $value ) )->forget( $key );
            }
        }

        // discrimination of fields to xlsx file
        $xlsxFields = $xlsxFields->only( array_keys( $this->propertyRepository->flattenedHeader() ) );

        // merge to avoid non-existent values
        $dictionary = array_fill_keys( array_keys( $this->propertyRepository->flattenedHeader() ), null );
        $xlsxRow = array_merge( $dictionary, $xlsxFields->toArray() );

        // formatting that needs to be done
        $formatting = [
            'publication_date' => function ( $value ) {
                return Carbon::createFromFormat( 'Y-m-d H:i:s', $value )->format( 'd-m-Y' );
            },
            'distance' => function ( $value ) {
                return (int)round( $value, 0 );
            }
        ];

        // format
        foreach ( $formatting as $key => $callable ) {
            if ( empty( $xlsxRow[ $key ] ) === false ) {
                $xlsxRow[ $key ] = $callable( $xlsxRow[ $key ] );
            }
        }

        return $xlsxRow;
    }
}
