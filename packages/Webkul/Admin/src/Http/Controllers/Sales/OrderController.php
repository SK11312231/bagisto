<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderConversationRepository;
use Webkul\Sales\Models\OrderConversation;

use Illuminate\Support\Facades\Event;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\Tree;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Requests\ConfigurationForm;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $_config;

    /**
     * OrderRepository object
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    protected $coreConfigRepository;

    protected $OrderConversation;

    protected $configTree;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Sales\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository, CoreConfigRepository $coreConfigRepository)
    {
        $this->middleware('admin');

        $this->_config = request('_config');

        $this->orderRepository = $orderRepository;

        $this->coreConfigRepository = $coreConfigRepository;

        $this->OrderConversation = new OrderConversation();

        $this->prepareConfigTree();
    }

    public function prepareConfigTree()
    {
        $tree = Tree::create();

        foreach (config('core') as $item) {
            $tree->add($item);
        }

        $tree->items = core()->sortItems($tree->items);

        $this->configTree = $tree;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view($this->_config['view']);
    }

    /**
     * Show the view for the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $order = $this->orderRepository->findOrFail($id);

        $getConversation = $this->OrderConversation->select([
            'id',
            'order_id',
            'customer_id',
            'customer_message',
            'admin_message',
            'status',
            'customer_message_at',
            'admin_messgae_at'
        ], "order_id = $order->id", 10, 0, 'id', 'ASC');

        $config = $this->configTree;
        return view($this->_config['view'], compact('order', 'getConversation', 'config'));
    }

    /**
     * Cancel action for the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $result = $this->orderRepository->cancel($id);

        if ($result) {
            session()->flash('success', trans('admin::app.response.cancel-success', ['name' => 'Order']));
        } else {
            session()->flash('error', trans('admin::app.response.cancel-error', ['name' => 'Order']));
        }

        return redirect()->back();
    }

    public function saveconversation($id){
        $order = $this->orderRepository->findOrFail($id);
        $this->OrderConversation->order_id = $order->id;
        $this->OrderConversation->customer_id = $order->customer_id;
        $this->OrderConversation->admin_message = request()->input('adminmessage');
        $this->OrderConversation->admin_messgae_at = date('Y-m-d H:i:s');
        

        if ($this->OrderConversation->save()) {
            session()->flash('success', trans('admin::app.response.message-success'));
        } else {
            session()->flash('error', trans('admin::app.response.message-error'));
        }
        
        return redirect()->back();
    }
}