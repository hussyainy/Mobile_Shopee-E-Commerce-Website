<?php

// php cart class
class Cart
{
    public $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con)) {
            return;
        }
        $this->db = $db;
    }

    // Add item to wishlist
    public function addToWishlist($userid, $itemid){
        if (isset($userid) && isset($itemid)){
            // Do not specify cart_id, let MySQL auto-increment
            $query_string = sprintf("INSERT INTO wishlist(user_id, item_id) VALUES (%d, %d)", $userid, $itemid);
            $result = $this->db->con->query($query_string);
            if ($result){
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
    }

    // insert into cart table
    public  function insertIntoCart($params = null, $table = "cart"){
        if ($this->db->con != null){
            if ($params != null){
                // "Insert into cart(user_id) values (0)"
                // get table columns
                $columns = implode(',', array_keys($params));

                $values = implode(',' , array_values($params));

                // create sql query
                $query_string = sprintf("INSERT INTO %s(%s) VALUES(%s)", $table, $columns, $values);

                // execute query
                $result = $this->db->con->query($query_string);
                return $result;
            }
        }
    }

    // to get user_id and item_id and insert into cart table
    public  function addToCart($userid, $itemid){
        if (isset($userid) && isset($itemid)){
            $params = array(
                "user_id" => $userid,
                "item_id" => $itemid
            );

            // insert data into cart
            $result = $this->insertIntoCart($params);
            if ($result){
                // Reload Page
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
    }

    // delete cart item using cart item id
    public function deleteCart($item_id = null, $table = 'cart'){
        if($item_id != null){
            $result = $this->db->con->query("DELETE FROM {$table} WHERE item_id={$item_id}");
            if($result){
                header("Location:" . $_SERVER['PHP_SELF']);
            }
            return $result;
        }
    }

    // calculate sub total
    public function getSum($arr){
        if(isset($arr)){
            $sum = 0;
            foreach ($arr as $item){
                $sum += floatval($item[0]);
            }
            return sprintf('%.2f' , $sum);
        }
    }

    // get item_it of shopping cart list
    public function getCartId($cartArray = null, $key = "item_id"){
        if ($cartArray != null){
            $cart_id = array_map(function ($value) use($key){
                return $value[$key];
            }, $cartArray);
            return $cart_id;
        }
    }

    // Save for later
    public function saveForLater($item_id = null, $saveTable = "wishlist", $fromTable = "cart"){
        if ($item_id != null){
            // Insert into wishlist from cart
            $insert_query = "INSERT INTO {$saveTable} (user_id, item_id) SELECT user_id, item_id FROM {$fromTable} WHERE item_id={$item_id}";
            $delete_query = "DELETE FROM {$fromTable} WHERE item_id={$item_id}";
            $insert_result = $this->db->con->query($insert_query);
            if ($insert_result) {
                $delete_result = $this->db->con->query($delete_query);
                if ($delete_result) {
                    header("Location: " . $_SERVER['PHP_SELF']);
                }
                return $delete_result;
            }
            return $insert_result;
        }
    }


}