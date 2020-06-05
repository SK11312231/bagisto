<?php

namespace Webkul\Sales\Models;

use Webkul\Checkout\Models\CartProxy;
use Illuminate\Database\Eloquent\Model;
use Webkul\Sales\Contracts\Order as OrderContract;
use DB;

class OrderConversation extends Model{
    protected $table = 'order_conversation';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public function select($values = array(), $where, $limit = 1, $offset = 0, $order = 'id', $dir = 'DESC') {
        DB::enableQueryLog();
        $orderconversation = DB::table($this->table)
                ->select($values)
                ->when($where, function($query) use ($where) {
                    return $query->whereRaw($where);
                })
                ->offset($offset)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        $query = DB::getQueryLog();
        return $orderconversation;
    }
}