<?php

register_rest_route("track-time/v1", "/invoice", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        global $wpdb;
        $invoice = $req->get_param("invoice");
        $current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
        $array = json_decode($current[0][0], true);
        return [
            "invoice"	=> $array,
            "number"	=> $invoice,
        ];
    }
]);

register_rest_route("track-time/v1", "/employee", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        global $wpdb;
        // $invoice	= $req->get_param("invoice");
        $date		= $req->get_param("date");
        // $date		= "2024-10-04";
        $whois		= $req->get_param("whois");
        $result 	= $wpdb->get_results("SELECT * FROM wp_track_time WHERE employee = {$whois} and DATE(date) = '{$date}'", ARRAY_N);
        // $array = json_decode($current[0][0], true);
        return [
            // "invoice"	=> $array,
            // "number"	=> $invoice,
            // "plain"		=> $current,
            "response"	=> $result,
        ];
    }
]);