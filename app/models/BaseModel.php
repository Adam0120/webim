<?php


namespace app\models;


use think\Model;

class BaseModel extends Model
{
    public static function upData($id,$data): bool
    {
        return self::where('id',$id)->update($data);
    }
}
