<?php


function line_discount_get_option_value( $opt_name , $def_val = '' )
{

    $info = get_instance()->db->select('value')->from(db_prefix().'options')->where('name',$opt_name)->get()->row();

    if ( !empty( $info->value ) )
        return $info->value;

    return $def_val;

}


function line_discount_update_option_value( $opt_name , $value )
{

    $CI = &get_instance();

    $info = $CI->db->select('id')->from(db_prefix().'options')->where('name',$opt_name)->get()->row();

    if ( empty( $info->id ) )
    {
        // New Record

        $CI->db->insert( db_prefix().'options' , [
            'name' => $opt_name ,
            'value' => $value ,
            'autoload' => 0
        ] );

    }
    else
    {

        // Upload

        $CI->db->where('id',$info->id)->update( db_prefix().'options' , [
            'value' => $value ,
            'autoload' => 0
        ] );

    }


}
