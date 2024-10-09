<?php

function get_by_invoice($invoice){
    global $wpdb;
    $result     = $wpdb->get_results("SELECT * FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
    return $result;
}

function get_by_employee($employee, $date){
    global $wpdb;
    $result 	= $wpdb->get_results("SELECT * FROM wp_track_time WHERE employee = {$employee} and DATE(date) = '{$date}'", ARRAY_N);
    return $result;
}

register_rest_route("track-time/v1", "/invoice", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        $result = get_by_invoice($req->get_param("invoice"));
        return [
            "response"	=> $result,
        ];
    }
]);

register_rest_route("track-time/v1", "/employee", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        $result = get_by_employee($req->get_param("whois"), $req->get_param("date"));
        return [
            "response"	=> $result,
        ];
    }
]);

register_rest_route("track-time/v1", "/invoice", [
    [
        "methods"	=> "POST",
        "callback"	=> function(WP_REST_Request $req){
            global $wpdb;
            $invoice			= $req->get_param("invoice");
            $whois				= $req->get_param("whois");
            $manTime			= $req->get_param("manTime");
            $start				= $req->get_param("start");
            if(!is_numeric($start)){
                $start 			= strtotime($start);
            }
            $trackArr 			= [];
            $trackArr["time"]	= $manTime ? $manTime : $req->get_param("time");
            $trackArr["notes"]	= $req->get_param("notes");
            $trackObj			= json_encode($trackArr);

            $query				= $wpdb->prepare("INSERT INTO wp_track_time (invoice, time, employee, date) VALUES ('{$invoice}', '{$trackObj}', {$whois}, FROM_UNIXTIME({$start}))");
            $success 			= $wpdb->query($query);

            return [
                "success"		=> $success,
            ];
        },
        "permission_callback"	=> function(){
            $nonce = sanitize_text_field($_SERVER["HTTP_X_WP_NONCE"]);
            if(wp_verify_nonce($nonce, "wp_rest")){
                return true;
            }
        }
    ],
    [
        "methods"	=> "DELETE",
        "callback"	=> function(WP_REST_Request $req){
            global $wpdb;

            $id			    = $req->get_param("id");
            $query		    = $wpdb->prepare("DELETE FROM wp_track_time WHERE id = {$id}");
            $success	    = $wpdb->query($query);

            $date           = $req->get_param("date");
            $result;
            if($date){
                $employee   = $req->get_param("employee");
                $result     = get_by_employee($employee, $date);
            }else{
                $invoice    = $req->get_param("invoice");
                $result     = get_by_invoice($invoice);
            }

            return [
                "success"	=> $success,
                "response"	=> $result,
            ];
        },
        "permission_callback"	=> function(){
            $nonce = sanitize_text_field($_SERVER["HTTP_X_WP_NONCE"]);
            if(wp_verify_nonce($nonce, "wp_rest")){
                return true;
            }
        }
    ],
]);