<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $table = "labels";
    protected $fillable = ['label_name'];

    /**
     * Function to get label by the label_id and user_id
     * passing label_id and user_id as parameters
     * 
     * @return array
     */
    public static function getLabelByLabelIdandUserId($label_id, $user_id)
    {
        $label = Label::where('id', $label_id)->where('user_id', $user_id)->first();
        return $label;
    }

    /**
     * Function to get label by the label_name and user_id
     * passing label_name and user_id as parameters
     * 
     * @return array
     */
    public static function getLabelByLabelNameandUserId($label_name, $user_id)
    {
        $label = Label::where('labelname', $label_name)->where('user_id', $user_id)->first();
        return $label;
    }

    /**
     * Function to get labels by the user_id
     * passing user_id as parameter
     * 
     * @return array
     */
    public static function getLabelsByUserId($user_id)
    {
        $label = Label::where('user_id', $user_id)->paginate(4);
        return $label;
    }


    /**
     * Function to create a new label
     * passing label_name as parameter
     * 
     * @return array
     */
    public static function createLabel($label_name, $user_id)
    {
        $label = new Label;
        $label->labelname = $label_name;
        $label->user_id = $user_id;
        $label->save();
        return $label;
    }

    /**
     * Function to update the label
     * passing label_id, label_name and user_id as parameters
     * 
     * @return array
     */
    public static function updateLabel($label_id, $label_name, $user_id)
    {
        $label = Label::where('id', $label_id)->where('user_id', $user_id)->first();
        $label->labelname = $label_name;
        $label->save();
        return $label;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function note()
    {
        return $this->belongsTo(Notes::class);
    }
    public function labelnote()
    {
        return $this->belongsTo(LabelNotes::class);
    }
}
