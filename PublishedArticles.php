<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use DB,Customhelp,Mail,Languagehelper;
use App\Models\Posts;
use App\Models\Post_contents;
use App\Models\Media;

class PublishedArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publishcron:articlespublishe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Message sent successfully...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
   /* public function __construct()
    {
        parent::__construct();
    }*/

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){

        $type=array('1' =>'News','2' =>'Articles','3' =>'Uaa','4' =>'Events','6' =>'Resolution','7' =>'Blog','13' =>'Resources');
        $lanaliasname=array('1'=>'en','2'=>'fr','3'=>'spa','4'=>'de','5'=>'ru','6'=>'ar');
       // $postdate='2018-06-09'; $posttime='11:00:15';
        $postdate=date('Y-m-d');$posttime=date('H:i:00');     
        $getposts=Posts::select('id','post_type_category','post_site')
                                ->where('post_status','future')
                                ->where(function ($query) use($postdate,$posttime){
                                    $query->where('post_date',$postdate);
                                    $query->where('post_time',$posttime);
                                })->get();

        $sent=false;
        $mailposttpye='';       
        if(!empty($getposts)){ 
            foreach($getposts as $getpost) {
                $message ='';

                Posts::where('id', $getpost->id)->update(['post_status' => 'publish','post_date' => $postdate,'post_time' => $posttime]);
                $mailposttpye.=$type[$getpost->post_type_category];             
                $postcontent=Post_contents::select('language_id','title')->where('post_id',$getpost->id)->where('title','<>','')->orderBy('language_id','ASC')->first();                
                if(!empty($postcontent)){                   
                    $artTitle=$postcontent->title; 
                    if($getpost->post_site=='1'){
                    //url($current_language.'/media_detail/'.$post->postId).'/'.stripslashes($this->string_sanitize($post->title));
                        $artLink= Languagehelper::ei_url().$lanaliasname[$postcontent->language_id].'/detail/'.$getpost->id.'/'.stripslashes(app('App\Http\Controllers\Controller')->string_sanitize($artTitle)); 
                    }else{
                        $artLink= Languagehelper::woe_url().$lanaliasname[$postcontent->language_id].'/woe_homepage/woe_detail/'.$getpost->id.'/'.stripslashes(app('App\Http\Controllers\Controller')->string_sanitize($artTitle));
                    }
                    $posttpye=$type[$getpost->post_type_category];
                    $message .= '<table align="left" cellpadding="2" cellspacing="1" style="font-size:small;font-family:Verdana;" width="100%">
                                    <tbody>
                                        <tr><td>Hello, Education International</td></tr>
                                        <tr><td></td></tr>
                                        <tr><td>Your '.$posttpye.', <b>'.$artTitle.'</b> just went live. </td></tr>
                                        <tr><td></td></tr>
                                        <tr><td>Spread the '.$posttpye.' by sharing your '.$posttpye.' with others! Here is the <a href="'.$artLink.'" target="_blank">link</a>.</td></tr>
                                        <tr><td><br />Thanks,<br />Education International</td></tr>
                                        <tr><td></td></tr>
                                        <tr><td>** Do not reply directly to this email.</td></tr>
                                    </tbody>
                                </table>';                     
                    $sent = Mail::send(['html' => 'inquiry_mail'],['messageuser'=>$message], function($ms) use ($mailposttpye){
                        $ms->from('Timo.Linsenmaier@ei-ie.org');                
                        $ms->to('cyblance.uttam@gmail.com');
                        $ms->to('vipul.cyblance@gmail.com');
                        $ms->subject('Education International - '.$mailposttpye.' was Published');              
                    });
                    if( $sent == true ) {
                        echo "Message sent successfully...";
                    }else {
                        echo "Message could not be sent...";
                    }
                }
            }           
        }       
    }
}
