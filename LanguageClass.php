<?php
namespace App\Helpers;
use App\Models\Language;
use App\Models\Tags;
use App\Models\Tags_language;
use App\Models\Posts;
use App\Models\Post_contents;
use App\Models\Media;	
use App\Models\Post_media;
use App\Models\Theme;
use App\Models\Theme_language;
use App\Models\Country;
use App\Models\Country_language;
use Config;
use DB;
use LaravelLocalization;
use Languagehelper;
use Illuminate\Support\Facades\Auth;
use Validater;

class LanguageClass{
	
	public static function newsletter_template_type(){        
        return array('1'=>'In Focus','2'=>'DC Bulletin','3'=>'EFAIDS Newsletter','4'=>'TradEducation',
        	'5'=>'Connect','6'=>'Higher Education','7'=>'ETUCE','8'=>'ETUCE Circular','9'=>'ETUCE archive',
        	'10'=>'Congress7','11'=>'EI Roundup','12'=>'CEEnet','13'=>'Refugees');        
    }
    	
	public static function ei_url(){
		$get_ei_domain = DB::table('subsites')->select('domain')->where('id','1')->first();		
		return (@$_SERVER['SERVER_NAME']=="192.168.1.101") ? \Request::root()."/" : $get_ei_domain->domain."/";		
	}
	public static function woe_url(){
		$get_woe_domain = DB::table('subsites')->select('domain')->where('id','1')->first();		
		return (@$_SERVER['SERVER_NAME']=="192.168.1.101") ? \Request::root()."/" : $get_woe_domain->domain."/";	
	}

	public static function get_host_url(){
		$get_host = @$_SERVER['HTTP_HOST'];
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
			$protocol  = "https://";						
		}else{
			$protocol  = "http://";	
		}
		return $protocol.$get_host."/";	
	}
	
	public static function meta_values($langid,$template_type,$post_id,$site_templates){

		$labelsArr = \Languagehelper::labels();	
		if($site_templates == 1){
			$site_templates = $labelsArr['Education International'];
		}else{
			$site_templates =$labelsArr['Worlds of Education'];
		}	
		if(!empty($post_id)){
			if($template_type=="template_page"){					
					$meta_post=DB::table('templates')->Join('template_languages', function($join){
						$join->on('templates.id','=','template_languages.template_id');                        
						})->where('templates.id',$post_id)	
						->where('template_languages.language_id',$langid)					                    
						->select('templates.id','templates.media_id','template_languages.seo_title','template_languages.seo_description','template_languages.seo_keyword','template_languages.name')->first();				
					
					if(!empty($meta_post->seo_title)){
						$meta_title=$meta_post->seo_title;									
					}else if(!empty($meta_post->name)){
						$meta_title=$meta_post->name." : ".$site_templates;					
					}else{
						$meta_title=$labelsArr['title_for_layout']." : ".$site_templates;
					}

					if(!empty($meta_post->seo_description)){
						$meta_description=$meta_post->seo_description;				
					}else{
						//$meta_description=$labelsArr['metatag_description'];					
						$meta_description="Please find ".$meta_post->name." of ".$site_templates;
					}

					if(!empty($meta_post->seo_keyword)){					
						$meta_keywords=$meta_post->seo_keyword;							
					}else{	
						if(!empty($meta_post->seo_title)){				
							$meta_keywords=$meta_post->seo_title.",".$site_templates;
						}else if(!empty($meta_post->name)){
							$meta_keywords=$meta_post->name.",".$site_templates;		
						}else{
							$meta_keywords=$site_templates;
						}					
					}

					if(!empty($meta_post->media_id)){	
						$Post_Media=Media::select('media_gallery.type','media_gallery.image')->where('media_gallery.id',$meta_post->media_id)->first();
						if(!empty($Post_Media)){
							if($Post_Media->type=='image'){					
								/*if(@getimagesize(url('/media_gallery/original_'.$Post_Media->image))){
									$meta_image=url('/media_gallery/original_'.$Post_Media->image);
								}else{*/
									$meta_image=url('/media_gallery/'.$Post_Media->image);		
								//}
							}elseif($Post_Media->type=='video_url'){
								$videotype=app('App\Http\Controllers\Controller')->videoType($Post_Media->image);
								if($videotype=='youtube') {
									$url_pieces = explode('/',$Post_Media->image);        										
									if($url_pieces[3]=="embed"){
										$eiid = $url_pieces[4];
									}else{
										$extract_id = explode('?v=', $url_pieces[3]);
										$eiid = $extract_id[1];
									}
									$meta_image = 'http://img.youtube.com/vi/'.$eiid.'/mqdefault.jpg';
								}else if($videotype=='vimeo') {
									$url_pieces = explode('/',$Post_Media->image);
									$eiid=($url_pieces[2] == 'player.vimeo.com') ? $url_pieces[4] : $url_pieces[3] ;        		
									$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$eiid.'.php'));		
									$meta_image= $hash[0]['thumbnail_large'];                               
								}else{
									$meta_image=url('img/no_image_detail.jpg/');
								}
							}else if($Post_Media->type=='video'){
								$explode_video = explode('.',$Post_Media->image);			
								$meta_image=url('/media_gallery/'.$Post_Media->image);
							}else{			
								$meta_image=url('img/no_image_detail.jpg/');
							}
						}else{
							$meta_image=url('img/no_image_detail.jpg/');						
						}
					}else{
						$meta_image=url('img/no_image_detail.jpg/');	
					}	
			}else if($template_type=="medias" || $template_type=="media_detail"){								
						
						$meta_post=DB::table('media_gallery')->leftJoin('media_gallery_languages', function($join){
						$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');                        
						})->where('media_gallery.id',$post_id)                    
						->select('media_gallery.type','media_gallery.image','media_gallery_languages.title','media_gallery_languages.caption')->first();
							
							if(!empty($meta_post->title)){
								$meta_title=$meta_post->title;								
							}else{
								$meta_title=$labelsArr['title_for_layout']." : ".$site_templates;						
							}
							if(!empty($meta_post->caption)){
								$meta_description=$meta_post->caption;
							}else{
								$meta_description=$labelsArr['metatag_description'];
							}

							if(!empty($meta_post->seo_keyword)){					
								$meta_keywords=$meta_post->seo_keyword.",".$site_templates;					
							}else{	
								if(!empty($meta_post->title)){				
									$meta_keywords=$meta_post->title.",".$site_templates;
								}else{
									$meta_keywords=$site_templates;
								}
							}
							if($meta_post->type=='image'){					
								/*if(@getimagesize(url('/media_gallery/original_'.$meta_post->image))){
									$meta_image=url('/media_gallery/original_'.$meta_post->image);
								}else{*/
									$meta_image=url('/media_gallery/'.$meta_post->image);		
								//}
							}elseif($meta_post->type=='video_url'){
								$videotype=app('App\Http\Controllers\Controller')->videoType($meta_post->image);
								if($videotype=='youtube') {
									$url_pieces = explode('/',$meta_post->image);        										
									if($url_pieces[3]=="embed"){
										$eiid = $url_pieces[4];
									}else{
										$extract_id = explode('?v=', $url_pieces[3]);
										$eiid = $extract_id[1];
									}
									$meta_image = 'http://img.youtube.com/vi/'.$eiid.'/mqdefault.jpg';
								}else if($videotype=='vimeo') {
									$url_pieces = explode('/',$meta_post->image);
									$eiid=($url_pieces[2] == 'player.vimeo.com') ? $url_pieces[4] : $url_pieces[3] ;        		
									$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$eiid.'.php'));		
									$meta_image= $hash[0]['thumbnail_large'];                               
								}else{
									$meta_image=url('img/no_image_detail.jpg/');
								}
							}else if($meta_post->type=='video'){
								$explode_video = explode('.',$meta_post->image);			
								$meta_image=url('/media_gallery/'.$meta_post->image);
							}else{			
								$meta_image=url('img/no_image_detail.jpg/');
							}			
			}else if($template_type=="detail_staff"){			
						$meta_post=Posts::Join('post_contents', function($join) {
							$join->on('post_contents.post_id','=','posts.id');
						})->select('posts.alias_name',					
						'posts.staff_profile_picture',
						'post_contents.title',				
						'post_contents.subtitle',
						'post_contents.teaser',
						'post_contents.text',
						'post_contents.seo_title',
						'post_contents.seo_description',
						'post_contents.seo_keyword')				
						->where('posts.id',$post_id)			
						->where('post_contents.language_id',$langid)						
						->first();
						if(!empty($meta_post->seo_title)){
							$meta_title=$meta_post->seo_title;
						}else if(!empty($meta_post->alias_name)){
							$meta_title=$meta_post->alias_name." : ".$site_templates;									
						}else if(!empty($meta_post->subtitle)){
							$meta_title=$meta_post->subtitle." : ".$site_templates;					
						}else{
							$meta_title=$labelsArr['title_for_layout']." : ".$site_templates;
						}

						if(!empty($meta_post->seo_description)){
							$meta_description=$meta_post->seo_description;
						}else if(!empty($meta_post->title)){
							$meta_description=$meta_post->title;
						}else if(!empty($meta_post->teaser)){
							$meta_description=$meta_post->teaser;
						}else if(!empty($meta_post->text)){
							$meta_description=strip_tags($meta_post->text);						
						}else{
							$meta_description=$labelsArr['metatag_description'];
						}

						if(!empty($meta_post->seo_keyword)){					
							$meta_keywords=$meta_post->seo_keyword;
						}else if(!empty($meta_post->title)){					
							$meta_keywords=$meta_post->title.",".$site_templates;							
						}else{	
							if(!empty($meta_post->subtitle)){				
								$meta_keywords=$meta_post->subtitle.",".$site_templates;
							}else if(!empty($meta_post->teaser)){
								$meta_keywords=$meta_post->teaser.",".$site_templates;		
							}else{
								$meta_keywords=$site_templates;
							}					
						}
						if(!empty($meta_post->staff_profile_picture)){					
							 $meta_image=url('public/img/user_profile_pictures/'.$meta_post->staff_profile_picture);
						}else{
							$meta_image=url('img/no_image_detail.jpg/');
						}
			}else{				
				if($template_type=="country_profile_section"){
					$post_ids = explode("-",$post_id);					
					$country_id=0;
					if(!empty($post_ids[0])){
						$country_id= $post_ids[0];
					}
					$section_id=0;
					if(!empty($post_ids[1])){
						 $section_id= $post_ids[1];
					}
					/*$name_country_get=DB::table('countries')->Join('country_languages', function($join){
						$join->on('countries.id','=','country_languages.country_id');                        
						})->where('countries.id',$post_ids[0])
						->where('country_languages.language_id',$langid)                     
						->select('countries.seo_title','countries.seo_description','countries.seo_keyword','country_languages.short_name' ,'country_languages.long_name')
						->first();*/

					 $name_country_get=DB::table('country_languages')->select('*')->where('language_id',$langid)->where('country_id',$post_ids[0])->first();
				//$name_country_get=DB::table('country_languages')->select('short_name')->where('language_id',$langid)->where('country_id',$post_id)->first();
					if(!empty($name_country_get->seo_title)){
						$meta_title=$name_country_get->seo_title;								
					}else{
						$meta_title=$name_country_get->short_name.' : '.$labelsArr['Worlds of Education'];										
					}

					if(!empty($name_country_get->seo_keyword)){
						$meta_keywords=$name_country_get->seo_keyword;								
					}else{
						$meta_keywords= $name_country_get->short_name.','.$labelsArr['Worlds of Education'];									
					}
					if(!empty($name_country_get->seo_description)){
						$meta_description=$name_country_get->seo_description;
					}else{

						  $country_sub_section =DB::table('country_profile_section')
							->Join('country_profile_sub_section', function($join) {
								$join->on('country_profile_section.id', '=', 'country_profile_sub_section.country_profile_section_id');
							})
							->select('country_profile_sub_section.description')							
							->where('country_profile_section.section_id',$section_id)
							->where('country_profile_section.country_id',$country_id)
							->where('country_profile_sub_section.language_id',$langid)
							->where(function ($query) {
			                        $query->where('country_profile_sub_section.title','<>','');
			                        $query->orWhere('country_profile_sub_section.description','<>','');                             
			                })
							->where('country_profile_sub_section.status','active')
							->where('country_profile_sub_section.status_flag','sub_title')								
							->orderBy('country_profile_sub_section.position','ASC')										
							->get();
						
							$description ='';
							foreach($country_sub_section as $k=>$sub_section){	

								if(strlen($sub_section->description)){
									$description= stripslashes(substr(strip_tags($sub_section->description),0,160)."....");	
									break;
								}									
							}							
							if(!empty($description)){
								$meta_description=$description;
							}else{
								$meta_description=$labelsArr['metatag_description'];
							}						
					}	
					
					$meta_image=url('img/no_image_detail.jpg/');					
				} else {			

				$meta_post=DB::table('post_contents')->select('post_id','seo_title','seo_description', 'seo_keyword','title','teaser','text')->where('post_id',$post_id)->where('language_id',$langid)->first();

						//print_r($meta_post); 				
					
					if(!empty($meta_post->seo_title)){
						$meta_title = $meta_post->seo_title;	
					}else if(!empty($meta_post->title)){
						$meta_title = $meta_post->title." : ".$site_templates;					
					}else{
						$meta_title= $labelsArr['title_for_layout']." : ".$site_templates;					
					}

					if(!empty($meta_post->seo_description)){
						$meta_description=$meta_post->seo_description;
					}else if(!empty($meta_post->teaser)){
						$meta_description=$meta_post->teaser;
					}else if(!empty($meta_post->text)){											
						$meta_description= stripslashes(substr(strip_tags($meta_post->text),0,160)."....");					
					}else{
						$meta_description=$labelsArr['metatag_description'];
					}

					if(!empty($meta_post->seo_keyword)){					
						$meta_keywords=$meta_post->seo_keyword;							
					}else{	
						if(!empty($meta_post->seo_title)){				
							$meta_keywords=$meta_post->seo_title.",".$site_templates;
						}else if(!empty($meta_post->title)){
							$meta_keywords=$meta_post->title.",".$site_templates;		
						}else{
							$meta_keywords=$site_templates;
						}					
					}									
					//$meta_keywords=$labelsArr['Education International'];

					if($template_type=="detail_page" || $template_type=="woe_detail_page"){				
						$metas_post=DB::table('posts')->select('media_id')->where('id',$post_id)->first();
						$Post_Media=Media::select('media_gallery.type','media_gallery.image');				
							if(!empty($meta_post->metas_post)){
								$Post_Media=$Post_Media->where('media_gallery.id',$meta_post->metas_post);                   	
							} 
						$Post_Media=$Post_Media->first();
					}else{				
						$Post_Media=Media::Join('post_media', function($join) {
								$join->on('post_media.media_id','=','media_gallery.id');
							})->select('media_gallery.type','media_gallery.image')							
							->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();
					}
					if(!empty($Post_Media)){				
						if($Post_Media->type=='image'){					
							/*if(@getimagesize(url('/media_gallery/original_'.$Post_Media->image))){
								$meta_image=url('/media_gallery/original_'.$Post_Media->image);
							}else{*/
								$meta_image=url('/media_gallery/'.$Post_Media->image);		
							//}
						}elseif($Post_Media->type=='video_url'){
							$videotype=app('App\Http\Controllers\Controller')->videoType($Post_Media->image);
							if($videotype=='youtube') {
								$url_pieces = explode('/',$Post_Media->image);
								//echo "<pre>";print_r($url_pieces);exit;
								if($url_pieces[3]=="embed"){
									$eiid = $url_pieces[4];
								}else{
									$extract_id = explode('?v=', $url_pieces[3]);
									$eiid = $extract_id[1];
								}												
								$meta_image = 'http://img.youtube.com/vi/'.$eiid.'/mqdefault.jpg';
							}else if($videotype=='vimeo') {								                            
								$meta_image=url('img/no_image_detail.jpg/');
							}else{
								$meta_image=url('img/no_image_detail.jpg/');
							}
						}else if($Post_Media->type=='video'){
							$explode_video = explode('.',$Post_Media->image);			
							$meta_image=url('/media_gallery/'.$Post_Media->image);
						}else{			
							$meta_image=url('img/no_image_detail.jpg/');
						}
					}else{				
						$meta_image=url('img/no_image_detail.jpg/');						
					}

				}
			}		
		}else{		
			$meta_title= $site_templates." - ".$labelsArr['title_for_layout'];
			$meta_description=$labelsArr['metatag_description'];
			$meta_keywords=$site_templates;
			$meta_image=url('img/no_image_detail.jpg/');			
		}		
		$meta_description= stripslashes(strip_tags($meta_description));	
		$meta_value_array= array($meta_title,$meta_description,$meta_keywords,$meta_image);
		return $meta_value_array;		
	}

	//This Function is Used For Homepage Slider
    public static function slider_media_home($post_id,$langid){

		$rp=str_replace('public', '', url('')); 					

		$Latest_Medias=Media::leftJoin('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->leftJoin('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();	
			
		if(!empty($Latest_Medias)){
			if($Latest_Medias['type']=='image'){
				if(!empty($Latest_Medias['caption'])){
					$caption=$Latest_Medias['caption'];
				}else{
					$caption="";
				}				
				if(@getimagesize(url('/media_gallery/original_'.$Latest_Medias['image']))){
					return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/'.trim('original_'.$Latest_Medias['image'])).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$caption.'">';
				}else{
					return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/'.trim($Latest_Medias['image'])).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$caption.'">';
				}				
			}elseif($Latest_Medias['type']=='video_url'){			
				$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
				if($videotype=='youtube'){                                
					return '<iframe width="100%" height="509px" src="'.str_replace("watch?v=","embed/",$Latest_Medias['image']).'" frameborder="0" allowfullscreen></iframe>';                                                                
				}else if($videotype=='vimeo'){
					$explode_vimeo = explode('/',$Latest_Medias['image']);
					if(@$explode_vimeo[2]=='vimeo.com'){
						$vm_id = $explode_vimeo[3];
						return '<iframe width="100%" height="509px" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
					}else{
						return '<iframe width="100%" height="509px" src="'.$Latest_Medias['image'].'" frameborder="0" allowfullscreen></iframe>';
					}                                
				}else{
					return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
				}
			}elseif($Latest_Medias['type']=='video'){
				$explode_video = explode('.',$Latest_Medias['image']);			
				return '<video id="slider_video_play" onclick="play_video(this,1);" width="100%" height="509px" controls><source src="'.url('/media_gallery/'.$Latest_Medias['image']).'" type="video/'.$explode_video[1].'"></video>';                                                                
			}else{			
				return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
			}	
		}else{			
			return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
		}		
    }
	
    //This Function is Used For Homepage Listing
    public static function media_home_search($post_id,$langid){
		$rp=str_replace('public', '', url(''));			
		$Latest_Medias=Media::Join('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->Join('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();
			//->where('media_gallery_languages.language_id','=',$langid)
			//->where('media_gallery.id','=',$media_id)						
		
		if(!empty($Latest_Medias)){
			if($Latest_Medias['type']=='image'){				
				if(!empty($Latest_Medias['caption'])){
					$caption=$Latest_Medias['caption'];
				}else{
					$caption="";
				}				
				/*if(@getimagesize(url('/media_gallery/original_'.$Latest_Medias['image']))){
					return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/'.trim('original_'.$Latest_Medias['image'])).'&amp;w=652&amp;h=368&amp;zc=1" alt="'.$caption.'">';
				}else{*/
					return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/'.trim($Latest_Medias['image'])).'&amp;w=652&amp;h=368&amp;zc=1" alt="'.$caption.'">';
				//}								
			}elseif($Latest_Medias['type']=='video_url'){			
				$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
				if($videotype=='youtube'){                                
					return '<iframe width="100%" height="291" src="'.str_replace("watch?v=","embed/",$Latest_Medias['image']).'" frameborder="0" allowfullscreen></iframe>';                                                                
				}else if($videotype=='vimeo') {
					$explode_vimeo = explode('/',$Latest_Medias['image']);
					if(@$explode_vimeo[2]=='vimeo.com'){
						$vm_id = $explode_vimeo[3];
						return '<iframe width="100%" height="291" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
					}else{
						return '<iframe width="100%" height="291" src="'.$Latest_Medias['image'].'" frameborder="0" allowfullscreen></iframe>';
					}                                
				}else{
					return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
				}
			}else if($Latest_Medias['type']=='video'){
				$explode_video = explode('.',$Latest_Medias['image']);			
				return '<video id="video_play_'.$post_id.'" onclick="play_video(this,'.$post_id.');" width="100%" height="291" controls><source src="'.url('/media_gallery/'.$Latest_Medias['image']).'" type="video/'.$explode_video[1].'"></video>';                                                                
			}else{			
				return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
			}	
		}else{			
			return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
		}
    }    

	//This Function is Used For Homepage Listing
    public static function media_home($post_id,$langid){ 

		$rp=str_replace('public', '', url(''));					
		$Latest_Medias=Media::leftJoin('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->leftJoin('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();				
		if(!empty($Latest_Medias)){
			if($Latest_Medias['type']=='image'){
				if(!empty($Latest_Medias['caption'])){
					$caption=$Latest_Medias['caption'];
				}else{
					$caption="";
				}  				
				return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/'.trim($Latest_Medias['image'])).'&amp;w=652&amp;h=368&amp;zc=1" alt="'.$caption.'">';				
			}elseif($Latest_Medias['type']=='video_url'){			
				$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
				if($videotype=='youtube'){                                
					return '<iframe width="100%" height="291" src="'.str_replace("watch?v=","embed/",$Latest_Medias['image']).'" frameborder="0" allowfullscreen></iframe>';                                                                
				}else if($videotype=='vimeo') {
					$explode_vimeo = explode('/',$Latest_Medias['image']);
					if(@$explode_vimeo[2]=='vimeo.com'){
						$vm_id = $explode_vimeo[3];
						return '<iframe width="100%" height="291" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
					}else{
						return '<iframe width="100%" height="291" src="'.$Latest_Medias['image'].'" frameborder="0" allowfullscreen></iframe>';
					}                                
				}else{
					return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
				}
			}else if($Latest_Medias['type']=='video'){
				$explode_video = explode('.',$Latest_Medias['image']);			
				return '<video id="video_play_'.$post_id.'" onclick="play_video(this,'.$post_id.');" width="100%" height="291" controls><source src="'.url('/media_gallery/'.$Latest_Medias['image']).'" type="video/'.$explode_video[1].'"></video>';                                                                
			}else{			
				return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
			}	
		}else{			
			return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
		}
    }
	
    public static function media_home_api($post_id,$langid){
		$rp=str_replace('public', '', url(''));			
		$Latest_Medias=Media::Join('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->Join('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();
			//->where('media_gallery_languages.language_id','=',$langid)
			//->where('media_gallery.id','=',$media_id)						
		
		if(!empty($Latest_Medias)){
			if($Latest_Medias['type']=='image'){								
				return  url('/media_gallery/'.$Latest_Medias['image']);
			}elseif($Latest_Medias['type']=='video_url'){			
				$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
				if($videotype=='youtube'){                                
					return str_replace("watch?v=","embed/",$Latest_Medias['image']);                                                                
				}else if($videotype=='vimeo') {
					$explode_vimeo = explode('/',$Latest_Medias['image']);
					if(@$explode_vimeo[2]=='vimeo.com'){
						$vm_id = $explode_vimeo[3];
						return "https://player.vimeo.com/video/'.$vm_id.'";                                                                
					}else{
						return $Latest_Medias['image'];
					}                                
				}else{
					return url('img/no_image.jpg/');
				}
			}else if($Latest_Medias['type']=='video'){
				$explode_video = explode('.',$Latest_Medias['image']);			
				return url('/media_gallery/'.$Latest_Medias['image']).'-video';
			}else{			
				return url('img/no_image.jpg/');
			}	
		}else{			
			return url('img/no_image.jpg/');
		}
    }
	
	//This Function is Used For All Listing Page Slider
	public static function slider_media_listing($post_id,$langid){
		$rp=str_replace('public', '', url('')); 					
		$Post_Media=Media::Join('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->Join('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->get();	
			//->where('media_gallery_languages.language_id','=',$langid)					
		$div='';
		if(count($Post_Media)>0){			
			foreach($Post_Media as $k=>$Latest_Medias){			
				if(!empty($Latest_Medias)){
					$class=($k==0)?'active':'';
					$div.= '<div class="item '.$class.'">';			
						if($Latest_Medias['type']=='image'){
							if(!empty($Latest_Medias['caption'])){
								$caption=$Latest_Medias['caption'];
							}else{
								$caption="";
							}						
							if(@getimagesize(url('/media_gallery/original_'.$Latest_Medias['image']))){
								$div.= '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/original_'.trim($Latest_Medias['image'])).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$caption.'">';
							}else{
								$div.= '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/'.trim($Latest_Medias['image'])).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$caption.'">';
							}						
						}elseif($Latest_Medias['type']=='video_url'){			
							$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
							if($videotype=='youtube'){                                
								$div.= '<iframe width="100%" height="509px" src="'.str_replace("watch?v=","embed/",$Latest_Medias['image']).'" frameborder="0" allowfullscreen></iframe>';                                                                
							}else if($videotype=='vimeo'){
								$explode_vimeo = explode('/',$Latest_Medias['image']);
								if(@$explode_vimeo[2]=='vimeo.com'){
									$vm_id = $explode_vimeo[3];
									$div.= '<iframe width="100%" height="509px" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
								}else{
									$div.= '<iframe width="100%" height="509px" src="'.$Latest_Medias['image'].'" frameborder="0" allowfullscreen></iframe>';
								}                                
							}else{
								$div.= '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
							}
						}else if($Latest_Medias['type']=='video'){
							$explode_video = explode('.',$Latest_Medias['image']);			
							$div.= '<video id="slider_video_play" onclick="play_video(this,1);" width="100%" height="509px" controls><source src="'.url('/media_gallery/'.$Latest_Medias['image']).'" type="video/'.$explode_video[1].'"></video>';                                                                
						}else{			
							$div.= '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
						}
					$div.= '</div>';
				}else{			
					$div.= '<div class="item active"><img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail"></div>';
				}
			}
		}else{			
			$div.= '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/no_image_detail.jpg/').'&amp;w=1200&amp;h=536&amp;zc=1" alt="no_image_detail">';
		}
		
		//Left and right controls
		if(!empty($Post_Media)){
			if(count($Post_Media)>1){
				$div.= '<nav class="custom_pagination">';
					$div.= '<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">';
                        $div.= '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>';
                    $div.= '</a>';					
					$div.= '<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">';
                        $div.= '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
                    $div.= '</a>';
				$div.= '</nav>';
			}
		}
		return $div;
    }
	
	//This Function is Used For Media Pages Slider
	public static function media_detail_slider($mediaimage){
		$videotype=app('App\Http\Controllers\Controller')->videoType($mediaimage);                            
		if($videotype=='youtube') {                                
			echo '<iframe width="100%" height="536px" src="'.str_replace("watch?v=","embed/",$mediaimage).'" frameborder="0" allowfullscreen></iframe>';                                                                
		}else if($videotype=='vimeo') {                    
			$explode_vimeo = explode('/',$mediaimage);
			if(@$explode_vimeo[2]=='vimeo.com'){
				$vm_id = $explode_vimeo[3];
				echo '<iframe width="100%" height="536px" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
			}else{
				echo '<iframe width="100%" height="536px" src="'.$mediaimage.'" frameborder="0" allowfullscreen></iframe>';
			}                                
		}else if($videotype=='video'){
			$explode_video = explode('.',$mediaimage);			
			echo '<video id="slider_video_play" onclick="play_video(this,1);" width="100%" height="536px" controls><source src="'.url('/media_gallery/'.$mediaimage).'" type="video/'.$explode_video[1].'"></video>';                                                                
		}else {
			$rp=str_replace('public', '', url(''));
            if(@getimagesize(url('/media_gallery/original_'.$mediaimage)))
            {
                echo '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/original_'.trim($mediaimage)).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$mediaimage.'"/>';                                                                                                                                                                                                                            
            }
            elseif(@getimagesize(url('/media_gallery/'.trim($mediaimage))))
            {
                echo '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php?src='.url('/media_gallery/'.trim($mediaimage)).'&amp;w=1200&amp;h=536&amp;zc=1" alt="'.$mediaimage.'"/>';                                                                                                                                                                                                                
            } else {
				echo '<img src="'.url('img/no_image_detail.jpg/').'" alt="no_image_detail">';
			}
		}
	}
	
	//This Function is Used in Search Page Media AND Media Listing Page
	public static function medias_listing($mediaimage,$media_id){
		$videotype=app('App\Http\Controllers\Controller')->videoType($mediaimage);		
		if($videotype=='youtube'){                                
			return '<iframe width="100%" height="291" src="'.str_replace("watch?v=","embed/",$mediaimage).'" frameborder="0" allowfullscreen></iframe>';                                                                
		}else if($videotype=='vimeo') {
			$explode_vimeo = explode('/',$mediaimage);
			if(@$explode_vimeo[2]=='vimeo.com'){
				$vm_id = $explode_vimeo[3];
				return '<iframe width="100%" height="291" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
			}else{
				return '<iframe width="100%" height="291" src="'.$mediaimage.'" frameborder="0" allowfullscreen></iframe>';
			}                                
		}else if($videotype=='unknown'){
			@$explode_video = explode('.',$mediaimage);			
			return '<video id="video_play_'.$media_id.'" onclick="play_video(this,'.$media_id.');" width="100%" height="291" controls><source src="'.url('/media_gallery/'.$mediaimage).'" type="video/'.@$explode_video[1].'"></video>';                                                                
		}else{
			return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
		}
	}
	
	public static function medias_listing_api($mediaimage){
		$videotype=app('App\Http\Controllers\Controller')->videoType($mediaimage);		
		if($videotype=='youtube'){                                
			return str_replace("watch?v=","embed/",$mediaimage);                                                                
		}else if($videotype=='vimeo') {
			$explode_vimeo = explode('/',$mediaimage);
			if(@$explode_vimeo[2]=='vimeo.com'){
				$vm_id = $explode_vimeo[3];
				return "https://player.vimeo.com/video/'.$vm_id.'";                                                                
			}else{
				return $mediaimage;
			}                                
		}else if($videotype=='unknown'){
			$explode_video = explode('.',$mediaimage);			
			return url('/media_gallery/'.$mediaimage).'-video';                                                                
		}else{
			return url('img/no_image.jpg/');
		}
	}
	
	public static function get_sidebar_tags($tagid,$langid,$home_page_tagcolours){    	
		$tags=Tags_language::select('tag_id','name')->whereIn('tag_id',explode(',',$tagid))->where('language_id',$langid)->orderBy('name','ASC')->get();
		
    	if (strpos(\Request::url(), 'woe_homepage') !== false) {
		    $site_type=2;
		} else {
			$site_type=1;
		}

    	if(count($tags)>0){
			$str='<ul id="meta_tags" class="meta_tags">';
                foreach($tags as $t=>$tags) {
					$str.='<li>';
                    $str.='<a href="'.url(Config::get('app.locale').'/search?srch-term=&site_type='.$site_type.'&tags='.$tags->tag_id).'" class="tag" style="background:'.$home_page_tagcolours[$t].'">'.$tags->name.'</a>';
					$str.='</li>';
                }                                                      
            $str.='</ul>';
            return $str;
		}
    }
	
    public static function get_tags($tagid,$langid,$home_page_tagcolours){
    	
    	$tags=Tags_language::select('tag_id','name')->whereIn('tag_id',explode(',',$tagid))->where('language_id',$langid)->orderBy('name','ASC')->get();
		/*$tags=DB::table('tags')->Join('tags_languages', function($join) {
				$join->on('tags_languages.tag_id','=','tags.id');
			})
			->select('tags_languages.tag_id','tags_languages.name')
			->where('tags.status','=','1')
			->whereIn('tags_languages.tag_id',explode(',',$tagid))
			->where('tags_languages.language_id',$langid)
			->orderBy('tags_languages.name','ASC')->get();*/
		
    	if (strpos(\Request::url(), 'woe_homepage') !== false) {
		    $site_type=2;
		} else {
			$site_type=1;
		}
    	if(count($tags)>0){
			$str='<ul id="meta_tags" class="meta_tags">';
                foreach($tags as $t=>$tags) {
					$str.='<li>';
                    $str.='<a href="'.url(Config::get('app.locale').'/search?srch-term=&site_type='.$site_type.'&tags='.$tags->tag_id).'" class="tag" style="background:'.$home_page_tagcolours[$t].'">'.$tags->name.'</a>';
					$str.='</li>';
                }                                                      
            $str.='</ul>';
            return $str;
		}
    }
	
	public static function get_themes($themeid,$langid,$home_page_tagcolours){
    	$themes=Theme_language::select('name')->whereIn('theme_id',explode(',',$themeid))->where('language_id',$langid)->orderBy('name','ASC')->get();		
    	if(count($themes)>0){
			$str='<ul id="meta_tags" class="meta_tags">';
                foreach($themes as $t=>$thm) {
					$str.='<li>';
                    $str.='<a class="tag" style="background:'.$home_page_tagcolours[$t].'">'.$thm->name.'</a>';
					$str.='</li>';
                }                                                      
            $str.='</ul>';
            return $str;
		}
    }
	
	public static function get_countries($countryid,$langid,$home_page_tagcolours){
    	$countries=Country_language::select('short_name')->whereIn('country_id',explode(',',$countryid))->where('language_id',$langid)->orderBy('short_name','ASC')->get();		
    	if(count($countries)>0){
			$str='<ul id="meta_tags" class="meta_tags">';
                foreach($countries as $t=>$con) {
					$str.='<li>';
                    $str.='<a class="tag" style="background:'.$home_page_tagcolours[$t].'">'.$con->short_name.'</a>';
					$str.='</li>';
                }                                                      
            $str.='</ul>';
            return $str;
		}
    }
	
    /*public static function get_detail_tags($tadid,$langid,$home_page_tagcolours){    	
		$tags=DB::table('tags')->Join('tags_languages', function($join) {
				$join->on('tags_languages.tag_id','=','tags.id');
			})
			->select('tags_languages.tag_id','tags_languages.name')
			->where('tags.status','=','1')
			->whereIn('tags_languages.tag_id',explode(',',$tadid))
			->where('tags_languages.language_id',$langid)
			->orderBy('tags_languages.name','ASC')->get();
			
    	if (strpos(\Request::url(), 'woe_homepage') !== false) {
		    $site_type=2;
		} else {
			$site_type=1;
		}
    	if(!empty($tags)){
			$str='<ul id="meta_tags" class="meta_tags">';
                foreach($tags as $t=>$tags) {
                    $str.=' <li><a href="'.url(Config::get('app.locale').'/search?srch-term=&site_type='.$site_type.'&tags='.$tags->tag_id).'" class="tag" style="background:'.$home_page_tagcolours[$t].'" >'.$tags->name.'</a></li>';
                }                                                      
            $str.='</ul>';
            return $str;
		}
    }*/
	
	//New Filter Function For all Tags,Themes,Countries
	public static function get_active_filters($langid,$Filter_tags,$Filter_themes,$Filter_country,$category,$site){
		$labelsArr = \Languagehelper::labels();		
		if(\Auth::user()) {
			$subsite_id=\Auth::user()->subsite_id;
		} else {
			$subsite_id="1";
		}
		//Tags
    	if($category=='home' || $category=='news' || $category=='uaas' || $category=='woe_home' || $category=='blogsandarticle' || $category=='ei-publications' || $category=='publications' || $category=='policy-briefs' || $category=='posters-and-infographics'){
			$get_tags = DB::table('posts')->Join('post_contents', function($join) {				
					$join->on('posts.id','=','post_contents.post_id');
				})
				->select('tag_id','theme_id','country_id')
				->where('post_type','post')			
				->where(function ($query){
					$query->where('post_status','publish');
					$query->orWhere('post_status','private');				
				})->where('subsite_id',$subsite_id);				
				if($category=='home'){
					$get_tags=$get_tags->where('post_site',$site);
					$get_tags=$get_tags->where('post_contents.language_id',$langid);
					$get_tags=$get_tags->where('post_contents.title','<>','');				
					$get_tags=$get_tags->where('post_contents.home_page_display','Yes');			
				}else if($category=='news'){
					$get_tags=$get_tags->where('post_type_category','1');
				}else if($category=='uaas'){
					$get_tags=$get_tags->where('post_type_category','3');
				}else if($category=='woe_home'){
					$get_tags=$get_tags->whereIN('posts.post_type_category',[1,2,7]);
					$get_tags=$get_tags->whereIN('posts.post_site',[2,3]);
				}else if($category=='blogsandarticle'){
					$get_tags=$get_tags->whereIN('posts.post_type_category',[2,7]);
					$get_tags=$get_tags->where('post_site',$site);
				}else if($category=='ei-publications' || $category=='publications' || $category=='policy-briefs' || $category=='posters-and-infographics'){
					if($category=="ei-publications"){
						$res_type="4";
					}else if($category=="publications"){
						$res_type="5";
					}else if($category=="policy-briefs"){
						$res_type="6";
					}else if($category=="posters-and-infographics"){
						$res_type="7";
					}
					$get_tags=$get_tags->where('post_site',$site);
					$get_tags=$get_tags->where('post_type_category','13');
					$get_tags=$get_tags->where('record_type_id',$res_type);
				}				
				$get_tags=$get_tags->get();
				//echo "subsite_id :".$subsite_id."post_site :".$site.$get_tags;exit;
		}else if($category=='medias' || $category=='videos' || $category=='documents'){
			$get_tags = DB::table('media_gallery')->leftJoin('media_gallery_languages', function($join) {
					$join->on('media_gallery_languages.media_gallery_id','=','media_gallery.id');
				})
				->select('media_gallery.tag_id')
				->where('media_gallery.subsite_id',$subsite_id)
				->where('media_gallery_languages.language_id',$langid)
				->where('media_gallery.tag_id','<>','')
				->where('media_gallery.media_site',$site);
				if($category=='medias' || $category=='videos'){
					$get_tags=$get_tags->whereIN('media_gallery.type',['video','video_url']);
				}else{
					$get_tags=$get_tags->whereIN('media_gallery.type',['excel','document','pdf','resource']);
				}																					
				$get_tags=$get_tags->get();
		}
		$tags=''; $themes=''; $country='';
		foreach($get_tags as $k=>$v){
			if(@$v->tag_id!=""){						
				$tg=str_replace(",,",",",$v->tag_id);
				$tags.=$tg.",";		
			}
			if(@$v->theme_id!=""){
				$tgt=str_replace(",,",",",$v->theme_id);
				$themes.=$tgt.",";
			}
			if(@$v->country_id!=""){
				$tgc=str_replace(",,",",",$v->country_id);
				$country.=$tgc.",";
			}
		}		
		//Tags
		$all_tags=substr($tags,0,-1);				
		if(!empty($all_tags)){
			$exp_tags=explode(",",$all_tags);
			$exp_tags=array_unique($exp_tags);
			$activetags=Tags_language::leftJoin('tags',function($join) {
				$join->on('tags.id','=','tags_languages.tag_id');
			})
			->select('tags_languages.tag_id','tags_languages.name')
			->where('tags.status','=','1')
			->where('tags_languages.language_id',$langid);
			if(count($exp_tags)==1){
				$activetags=$activetags->whereRaw('FIND_IN_SET('.$exp_tags[0].',tags.id)');
			} else {
				$newval="(";
				foreach($exp_tags as $val){
					$newval.='FIND_IN_SET('.$val.',tags.id) OR ';
				}
				$vals=substr($newval,0,-3);
				$activetags=$activetags->whereRaw($vals.')');
			}	
			
			$activetags=$activetags->orderBy('tags_languages.name','ASC');
			$activetags=$activetags->get();						
		}else{
			$activetags="";
		}
		//Themes
		$all_themes=substr($themes,0,-1);						
		if(!empty($all_themes)){
			$exp_themes=explode(",",$all_themes);
			$exp_themes=array_unique($exp_themes);			
			$activethemes=Theme_language::leftJoin('themes',function($join) {
				$join->on('themes.id','=','theme_languages.theme_id');
			})
			->select('theme_languages.theme_id','theme_languages.name')
			->where('themes.status','=','1')
			->where('theme_languages.language_id',$langid);
			if(count($exp_themes)==1)
			{
				$activethemes=$activethemes->whereRaw('FIND_IN_SET('.$exp_themes[0].',themes.id)');
			} else {			
				$newval_theme="(";
				foreach($exp_themes as $val){
					$newval_theme.='FIND_IN_SET('.$val.',themes.id) OR ';
				}
				$vals_theme=substr($newval_theme,0,-3);
				$activethemes=$activethemes->whereRaw($vals_theme.')');				
			}
			$activethemes=$activethemes->orderBy('theme_languages.name','ASC');
			$activethemes=$activethemes->get();						
		}else{
			$activethemes="";
		}
		//Country						
		$all_country=substr($country,0,-1);						
		if(!empty($all_country)){
			$exp_countries=explode(",",$all_country);
			$exp_countries=array_unique($exp_countries);			
			$activecountry=Country_language::leftJoin('countries',function($join) {
				$join->on('countries.id','=','country_languages.country_id');
			})
			->select('country_languages.country_id','country_languages.short_name')			
			->where('country_languages.language_id',$langid);
			if(count($exp_countries)==1){
				$activecountry=$activecountry->whereRaw('FIND_IN_SET('.$exp_countries[0].',countries.id)');
			} else {
				$newval_countries="(";
				foreach($exp_countries as $val){
					$newval_countries.='FIND_IN_SET('.$val.',countries.id) OR ';
				}
				$vals_countries=substr($newval_countries,0,-3);
				$activecountry=$activecountry->whereRaw($vals_countries.')');
			}
			$activecountry=$activecountry->orderBy('country_languages.short_name','ASC');
			$activecountry=$activecountry->get();						
		}else{
			$activecountry="";	
		}
		if(!empty($activetags || $activethemes || $activecountry)){
			$str='<div class="btn-group">';
                $str.='<button id="NEWS_FILTER" type="button" class="multiselect btn btn-default" title="Filter">';
					$str.='<span class="multiselect-selected-text">'.$labelsArr['Filter'].'</span>'; 
					$str.='<i class="fa fa fa-plus" aria-hidden="true"></i>';
				$str.='</button>';
				$str.='<div id="CUSTOM_NEWS_FILTER" class="custom_news_filter">';
					$str.='<div class="news_wrapper">';
						$str.='<div class="news_inner">';
							$str.='<ul class="nav nav-tabs">';
								$act_class_tag="";$act_class_theme="";$act_class_country="";
								if(!empty($activetags && $activethemes && $activecountry) || ($activetags && $activethemes) || ($activetags && $activecountry) || ($activetags)){
									$act_class_tag='active';
								}else if(!empty($activethemes && $activecountry) || ($activethemes)){
									$act_class_theme='active';
								}else if(!empty($activecountry)){
									$act_class_country='active';
								}
								if(!empty($activetags)){
									$str.='<li class="'.$act_class_tag.'"><a href="javascript:void(0);" id="tagstab" onclick="themestab(1);">'.$labelsArr['Tags'].'</a></li>';	
								}
								if(!empty($activethemes)){
									$str.='<li class="'.$act_class_theme.'"><a href="javascript:void(0);" id="themestab" onclick="themestab(2);">'.$labelsArr['Themes'].'</a></li>';	
								}
								if(!empty($activecountry)){
									$str.='<li class="'.$act_class_country.'"><a href="javascript:void(0);" id="countrytab" onclick="themestab(3);">'.$labelsArr['Country'].'</a></li>';	
								}								
							$str.='</ul>';
							$str.='<div class="tab-content" id="mainfilter">';
                                $str.='<span class="MianFilterLink" style="display:none">'.$labelsArr['Filter'].'</span>';
                                $str.='<span class="ClearFilterLink" style="display:none">'.$labelsArr['Clear'].'</span>';
								if(!empty($activetags)){
									$str.='<ul id="menu11" class="'.$act_class_tag.' multiselect-container dropdown-menu tab-pane">';																	
										if(!empty($Filter_tags)){											
											if(count($activetags)==count(explode(",",$Filter_tags))){
												$checkedallta='checked="checked"';												
											}else{
												$checkedallta='';												
											}
										}else{											
											$checkedallta='';											
										}										
										$str.='<li class="multiselect-item multiselect-all">';
											$str.='<a tabindex="0" class="multiselect-all">';
												$str.='<label class="checkbox"><input type="checkbox" id="tagallchk" value="multiselect-all" '.$checkedallta.'> '.$labelsArr['Check all'].'</label>';
											$str.='</a>';
										$str.='</li>';
										foreach($activetags as $active_tag){				
											if(in_array($active_tag['tag_id'],explode(",",$Filter_tags))){
												$checkedta='checked="checked"';												
											}else{
												$checkedta='';												
											}											
											$str.='<li>';
												$str.='<a tabindex="0"><label class="checkbox"><input type="checkbox" class="tagchk" value="'.$active_tag['tag_id'].'" '.$checkedta.'>'.$active_tag['name'].'</label></a>';
											$str.='</li>';
										}
									$str.='</ul>';							
								}								
								if(!empty($activethemes)){
									$str.='<ul id="menu12" class="'.$act_class_theme.' multiselect-container dropdown-menu tab-pane">';									
										if(!empty($Filter_themes)){
											if(count($activethemes)==count(explode(",",$Filter_themes))){
												$checkedallth='checked="checked"';												
											}else{
												$checkedallth='';												
											}	
										}else{
											$checkedallth='';											
										}										
										$str.='<li class="multiselect-item multiselect-all">';
											$str.='<a tabindex="0" class="multiselect-all">';
												$str.='<label class="checkbox"><input type="checkbox" id="themeallchk" value="multiselect-all" '.$checkedallth.'> '.$labelsArr['Check all'].'</label>';
											$str.='</a>';
										$str.='</li>';
										foreach($activethemes as $active_theme){				
											if(in_array($active_theme['theme_id'],explode(",",$Filter_themes))){
												$checkedth='checked="checked"';												
											}else{
												$checkedth='';												
											}											
											$str.='<li>';
												$str.='<a tabindex="0"><label class="checkbox"><input type="checkbox" class="themechk" value="'.$active_theme['theme_id'].'" '.$checkedth.'>'.$active_theme['name'].'</label></a>';
											$str.='</li>';
										}
									$str.='</ul>';
								}                                								
								if(!empty($activecountry)){
									$str.='<ul id="menu13" class="'.$act_class_country.' multiselect-container dropdown-menu tab-pane">';									
										if(!empty($Filter_country)){
											if(count($activecountry)==count(explode(",",$Filter_country))){
												$checkedallco='checked="checked"';												
											}else{
												$checkedallco='';												
											}	
										}else{
											$checkedallco='';											
										}										
										$str.='<li class="multiselect-item multiselect-all">';
											$str.='<a tabindex="0" class="multiselect-all">';
												$str.='<label class="checkbox"><input type="checkbox" id="countryallchk" value="multiselect-all" '.$checkedallco.'> '.$labelsArr['Check all'].'</label>';
											$str.='</a>';
										$str.='</li>';
										foreach($activecountry as $active_coun){				
											if(in_array($active_coun['country_id'],explode(",",$Filter_country))){
												$checkedco='checked="checked"';												
											}else{
												$checkedco='';												
											}											
											$str.='<li>';
												$str.='<a tabindex="0"><label class="checkbox"><input type="checkbox" class="countrychk" value="'.$active_coun['country_id'].'" '.$checkedco.'>'.$active_coun['short_name'].'</label></a>';
											$str.='</li>';
										}
									$str.='</ul>';
								}                                								
							$str.='</div>';	
						$str.='</div>';
					$str.='</div>';
				$str.='</div>';
			$str.='</div>';	
		}else{
			$str='';	
		}		
		return $str;	
    }
	
    public static function get_active_tags($langid,$Filter_tags,$category,$site){
		if(\Auth::user()) {
			$subsite_id=\Auth::user()->subsite_id;
		} else {
			$subsite_id="1";
		}		
    	if($category=='uaas' || $category=='news' || $category=='blogsandarticle' || $category=='resolutions' || $category=='constitution' || $category=='statements' || $category=='ei-publications' || $category=='publications' || $category=='policy-briefs' || $category=='posters-and-infographics'){
			$get_tags = DB::table('posts')->select('tag_id')->where('post_type','post')			
				->where(function ($query){
					$query->where('post_status','publish');
					$query->orWhere('post_status','private');				
				})->where('subsite_id',$subsite_id)->where('tag_id','!=','')->where('post_site',$site);
				if($category=='uaas'){
					$get_tags=$get_tags->where('post_type_category','3');
				}
				else if($category=='news'){
					$get_tags=$get_tags->where('post_type_category','1');
				}else if($category=='resolutions' || $category=='constitution' || $category=='statements'){
					if($category=="resolutions" || $category==""){
						$res_type="2";
					}else if($category=="constitution"){
						$res_type="1";
					}else if($category=="statements"){
						$res_type="3";
					}
					$get_tags=$get_tags->where('post_type_category','6');
					$get_tags=$get_tags->where('record_type_id',$res_type);
				}else if($category=='ei-publications' || $category=='publications' || $category=='policy-briefs' || $category=='posters-and-infographics'){
					if($category=="ei-publications"){
						$res_type="4";
					}else if($category=="publications"){
						$res_type="5";
					}else if($category=="policy-briefs"){
						$res_type="6";
					}else if($category=="posters-and-infographics"){
						$res_type="7";
					}
					$get_tags=$get_tags->where('post_type_category','13');
					$get_tags=$get_tags->where('record_type_id',$res_type);
				}else{
					$get_tags=$get_tags->whereIn('post_type_category',[2,7]);
				}						
				$get_tags=$get_tags->groupBy('tag_id');	
				$get_tags=$get_tags->get();
		}else if($category=='medias' || $category=='videos' || $category=='documents'){
			$get_tags = DB::table('media_gallery')->leftJoin('media_gallery_languages', function($join) {
					$join->on('media_gallery_languages.media_gallery_id','=','media_gallery.id');
				})
				->select('media_gallery.tag_id')
				->where('media_gallery.subsite_id',$subsite_id)
				->where('media_gallery_languages.language_id',$langid)
				->where('media_gallery.tag_id','!=','')
				->where('media_gallery.media_site',$site);
				if($category=='medias' || $category=='videos'){
					$get_tags=$get_tags->whereIN('media_gallery.type',['video','video_url']);
				}else{
					$get_tags=$get_tags->whereIN('media_gallery.type',['excel','document','pdf','resource']);
				}																	
				$get_tags=$get_tags->groupBy('media_gallery.tag_id');
				$get_tags=$get_tags->get();
		}
		
		$tags='';
		foreach($get_tags as $k=>$v){						
			$tg=str_replace(",,",",",$v->tag_id);
			$tags.=$tg.",";		
		}
		$all_tags=substr($tags,0,-1);		
		
		if(!empty($all_tags)){
			$exp_tags=explode(",",$all_tags);
			$exp_tags=array_unique($exp_tags);
			$activetags=Tags_language::leftJoin('tags',function($join) {
				$join->on('tags.id','=','tags_languages.tag_id');
			})
			->select('tags_languages.tag_id','tags_languages.name')
			->where('tags.status','=','1')
			->where('tags_languages.language_id',$langid);
			if(count($exp_tags)>1){
				$newval="(";
				foreach($exp_tags as $val){
					$newval.='FIND_IN_SET('.$val.',tags.id) OR ';
				}
				$vals=substr($newval,0,-3);
				$activetags=$activetags->whereRaw($vals.')');
			} else {
				$activetags=$activetags->whereRaw('FIND_IN_SET('.$all_tags.',tags.id)');
			}
			$activetags=$activetags->orderBy('tags_languages.name','ASC');
			$activetags=$activetags->get();			
					
			if(!empty($activetags)){			
				$str='<select id="example-onDropdownHidden" multiple="multiple">';									
					foreach($activetags as $active_tag){				
						if(in_array($active_tag['tag_id'],explode(",",$Filter_tags))){
							$checked='selected="selected"';	
						}else{
							$checked='';	
						}					
						$str.='<option value="'.$active_tag['tag_id'].'" '.$checked.'>'.$active_tag['name'].'</option>';										
					}
				$str.='</select>';			
				return $str;
			}	
		}			
    }		
	
    public static function languages(){
        return Language::select('*')->where('is_active',1)->get()->toArray();                  
    }	
    public static function years(){        
        return array('1'=>'1931','2'=>'1932','3'=>'1933','4'=>'1934','5'=>'1935','6'=>'1936','7'=>'1937','8'=>'1938','9'=>'1939','10'=>'1940','11'=>'1941','12'=>'1942','13'=>'1943','14'=>'1944','15'=>'1945','16'=>'1946','17'=>'1947','18'=>'1948','19'=>'1949','20'=>'1950','21'=>'1951','22'=>'1952','23'=>'1953','24'=>'1954','25'=>'1955','26'=>'1956','27'=>'1957','28'=>'1958','29'=>'1959','30'=>'1960','31'=>'1961','32'=>'1962','33'=>'1963','34'=>'1964','35'=>'1965','36'=>'1966','37'=>'1967','38'=>'1968','39'=>'1969','40'=>'1970','41'=>'1971','42'=>'1972','43'=>'1973','44'=>'1974','45'=>'1975','46'=>'1976','47'=>'1977','48'=>'1978','49'=>'1979','50'=>'1980','51'=>'1981','52'=>'1982','53'=>'1983','54'=>'1984','55'=>'1985','56'=>'1986','57'=>'1987','58'=>'1988','59'=>'1989','60'=>'1990','61'=>'1991','62'=>'1992','63'=>'1993','64'=>'1994','65'=>'1995','66'=>'1996','67'=>'1997','68'=>'1998','69'=>'1999','70'=>'2000','71'=>'2001','72'=>'2002','73'=>'2003','74'=>'2004','75'=>'2005','76'=>'2006','77'=>'2007','78'=>'2008','79'=>'2009','80'=>'2010','81'=>'2011','82'=>'2012','83'=>'2013','84'=>'2014','85'=>'2015','86'=>'2016','87'=>'2017','88'=>'2018','89'=>'2019','90'=>'2020');        
    }	    
    public static function post_category(){
    	//return array('1' =>'News','2' =>'Articles','4' =>'Events','6' =>'Resolution','7' =>'Blog','13' =>'Resources');
    	return array('1' =>'News','2' =>'Articles','3' =>'Uaa','4' =>'Events','6' =>'Resolution','7' =>'Blog','13' =>'Resources');	
    }	
    public static function post_category_value(){
		return array('0' =>'1','1' =>'2','2' =>'3','3' =>'4','5' =>'6','6' =>'7','7' =>'8','8' =>'13');        
    }
	
    public static function labels(){
    	/*if(Config::get('app.locale') =='en' || Config::get('app.locale') =='spa' || Config::get('app.locale') =='fr'){
    		 $langid=Language::where('alias_name',Config::get('app.locale'))->first()->id;
    	}else{*/
    		// $langid=Language::where('alias_name','en')->first()->id;
    	//}
    	$langid=Language::where('alias_name',Config::get('app.locale'))->first()->id;     
        $fetch_labels_array = DB::table('language_label')->leftJoin('label_translate','language_label.id', '=', 'label_translate.label_id')
        	->select('language_label.id as LabelID', 'language_label.label', 'label_translate.*')
			->where('language_label.is_active', '=', 1)->where('label_translate.language_id', '=', $langid)->get(); 		        
        foreach($fetch_labels_array as $labels){            
            $fetch_labels[$labels->label]=$labels->label_translate;	
        }        
        return $fetch_labels;		
    }
	
	public static function labels_api($langid){        
        $fetch_labels_array = DB::table('language_label')->join('label_translate','language_label.id', '=', 'label_translate.label_id')            
            ->select('language_label.id as LabelID', 'language_label.label', 'label_translate.*')
			->where('language_label.is_active', '=', 1)->where('label_translate.language_id', '=', $langid)->get();            
        foreach($fetch_labels_array as $labels){            
            $fetch_labels[$labels->label]=$labels->label_translate;	
        }           
        return $fetch_labels;		
    }
		
	public static function ILO_conventions(){
        return array('1' =>'29','2' =>'87','3' =>'98','4' =>'100','5' =>'105','6' =>'111','7' =>'138','8' =>'169','9' =>'182');
    }
	
    public static function barometer_titles(){
        return array('1' =>'Introduction','2' =>'Education Rights (General)','3' =>'Education Rights (Early Childhood)','4' =>'Education Rights (Primary)','5' =>'Education Rights (Secondary)',
			'6' =>'Education Rights (Tertiary)','7' =>'Education Rights (Special Needs)','8' =>'Education Rights (Refugees)','9' =>'Education Rights (Minorities)','10' =>'Academic Freedom',
			'11' =>'Gender Parity','12' =>'Child Labour','13' =>'Trade Union Rights','14' =>'Footnote');
    }
	
	public static function statistics_ranking_text(){
        return array('preprimary_t' =>'preprimary_t_text','preprimary_f' =>'preprimary_f_text','preprimary_p' =>'preprimary_p_text','preprimary_ger' =>'preprimary_ger_text','preprimary_ner' =>'preprimary_ner_text',
			'primary_t' =>'primary_t_text','primary_f' =>'primary_f_text','primary_p' =>'primary_p_text','primary_ger' =>'primary_ger_text','primary_ner' =>'primary_ner_text',
			'primarycohort_mf' =>'primarycohort_mf_text','primarycohort_f' =>'primarycohort_f_text',
			'secondary_t' =>'secondary_t_text','secondary_f' =>'secondary_f_text','secondary_p' =>'secondary_p_text','secondary_ger' =>'secondary_ger_text','secondary_ner' =>'secondary_ner_text',
			'tertiary_t' =>'tertiary_t_text','tertiary_f' =>'tertiary_f_text','tertiary_p' =>'tertiary_p_text','tertiary_ger' =>'tertiary_ger_text',
			'primary_ptr' =>'primary_ptr_text','secondary_ptr' =>'secondary_ptr_text','education_gdp' =>'education_gdp_text','education_expenditure' =>'education_expenditure_text'
		);
	}
	
	public static function inquiry_form_array(){
		return array('1'=>'first_name','2'=>'last_name','3'=>'company_name','4'=>'email','5'=>'phone','6'=>'city_of_interest','7'=>'address','8'=>'country','9'=>'state','10'=>'zip_code','11'=>'hear_about_us','12'=>'net_worth','13'=>'liquidity','14'=>'question_for_region_office','15'=>'subject_for_your_question','16'=>'message',);		
	}
	
	public static function inquiry_email_array(){
		if(\Auth::user()) {
			$subsite_id=\Auth::user()->subsite_id;
		} else {
			$subsite_id="1";
		}
		$langid=Language::where('alias_name',Config::get('app.locale'))->first()->id;
		$fetch_inquiry = DB::table('subsites')->select('*')->where('id',$subsite_id)->first();
		$fetch_admin_email = DB::table('users')->select('email')->where('id',$fetch_inquiry->user_id)->first();
		$admin_email = $fetch_admin_email->email;						
		$too_email= @$fetch_inquiry->inquiry_email;
		$from_email = @$fetch_inquiry->from_inquiry_email;
		
		if($fetch_inquiry->cc_email!=''){
			$cc_email = $fetch_inquiry->cc_email;
		}else{
			$cc_email = '';
		}
		if($fetch_inquiry->bcc_email!=''){
			$bcc_email = $fetch_inquiry->bcc_email;
		}else{
			$bcc_email = '';
		}
		$fetch_inquiry_lang = DB::table('subsites_languages')->select('*')->where('subsite_id',$subsite_id)->where('language_id',$langid)->first();			
		$inq_subject = $fetch_inquiry_lang->inquiry_subject;
		$inq_message = $fetch_inquiry_lang->inquiry_message;		
		if($too_email!=''){								
			$to_email= trim($too_email);					
		}else{
			$to_email= trim($admin_email);				
		}		
		$inq_email_array= array('admin_email'=>$admin_email,'to_email'=>$to_email,'from_email'=>$from_email,'inq_subject'=>$inq_subject,'inq_message'=>$inq_message,'cc_email'=>$cc_email,'bcc_email'=>$bcc_email);
		return $inq_email_array;		
	}
	
	public static function alphabets(){
		$labelsArr = Languagehelper::labels();		
		$alpha=array('0'=>$labelsArr['A'],'1'=>$labelsArr['B'],'2'=>$labelsArr['C'],'3'=>$labelsArr['D'],'4'=>$labelsArr['E'],'5'=>$labelsArr['F'],
			'6'=>$labelsArr['G'],'7'=>$labelsArr['H'],'8'=>$labelsArr['I'],'9'=>$labelsArr['J'],'10'=>$labelsArr['K'],
			'11'=>$labelsArr['L'],'12'=>$labelsArr['M'],'13'=>$labelsArr['N'],'14'=>$labelsArr['O'],'15'=>$labelsArr['P'],
			'16'=>$labelsArr['Q'],'17'=>$labelsArr['R'],'18'=>$labelsArr['S'],'19'=>$labelsArr['T'],'20'=>$labelsArr['U'],
			'21'=>$labelsArr['V'],'22'=>$labelsArr['W'],'23'=>$labelsArr['X'],'24'=>$labelsArr['Y'],'25'=>$labelsArr['Z']);
		return $alpha;
	}

	public static function staff_categories_name($category_id,$language_id){
		$staff_categories = \App\Models\Staff_categories_language::select('name')->where('staff_category_id',$category_id)->where('language_id',$language_id)->first();
		if(!empty($staff_categories))
		{
			return $staff_categories->name;	
		}else {
			return true;	
		}
	}
	
	public static function staff_categories(){
		$labelsArr = Languagehelper::labels();
		$staff_categories = \App\Models\Staff_categories::where('type','1')->where('status','1')->orderBy('position', 'asc')->get();
		foreach ($staff_categories as $key => $cat) {
			$staff_cat[$cat->id]=$cat->alias_name;
		}
		/*$staff_cat = array('1' =>'Management','2' =>'Senior Consultants','3' =>'Education and Employment Unit','4' =>'Research Unit','5' =>'Human and Trade Union Rights and Equality Unit',
			'6' =>'Solidarity and Development Unit','7' =>'Communications Unit','8' =>'Financial Services Unit','9' =>'Administrative Services Unit','10' =>'African Regional Office',
			'11' =>'Arab Countries Sub-Regional Office','12' =>'Asia-Pacific Regional Office','13' =>'European Regional Office (ETUCE)','14' =>'Latin American Regional Office','15' => 'Consultants'
		);*/
		
		return $staff_cat;
	}
	
	public static function executive_categories(){
		$labelsArr = Languagehelper::labels();				
		//$executive_cat = array('1' =>'Officers','2' =>'Regional Seats','3' =>'Open Seats');
		$staff_categories = \App\Models\Staff_categories::where('type','2')->where('status','1')->orderBy('position', 'asc')->get();
		foreach ($staff_categories as $key => $cat) {
			$executive_cat[$cat->id]=$cat->alias_name;
		}
		return $executive_cat;
	}
    
	public static function post_sites(){
    	return array('1' =>'EI','2' =>'WOE','3' =>'Resources');	
    }
	
	//This Function is Used For WOE Resourse Listing Page
    public static function woe_resource_media($post_id,$langid){ 
		$rp=str_replace('public', '', url(''));					
		$Latest_Medias=Media::leftJoin('media_gallery_languages', function($join){
				$join->on('media_gallery.id','=','media_gallery_languages.media_gallery_id');				
			})->leftJoin('post_media', function($join) {
				$join->on('post_media.media_id','=','media_gallery.id');
			})->select('media_gallery.type','media_gallery.image','media_gallery_languages.caption')
			->where('post_media.type','slidshow')->where('post_media.post_id',$post_id)->where('post_media.language_id',$langid)->first();				
		if(!empty($Latest_Medias)){
			if($Latest_Medias['type']=='image'){
				if(!empty($Latest_Medias['caption'])){
					$caption=$Latest_Medias['caption'];
				}else{
					$caption="";
				}  				
				return '<img src="'.$rp.'/resources/views/admin/medias/timthumb.php&#63;src='.url('/media_gallery/'.trim($Latest_Medias['image'])).'&amp;w=370&amp;h=290&amp;zc=1" alt="'.$caption.'">';				
			}elseif($Latest_Medias['type']=='video_url'){			
				$videotype=app('App\Http\Controllers\Controller')->videoType($Latest_Medias['image']);                            
				if($videotype=='youtube'){                                
					return '<iframe width="100%" height="291" src="'.str_replace("watch?v=","embed/",$Latest_Medias['image']).'" frameborder="0" allowfullscreen></iframe>';                                                                
				}else if($videotype=='vimeo') {
					$explode_vimeo = explode('/',$Latest_Medias['image']);
					if(@$explode_vimeo[2]=='vimeo.com'){
						$vm_id = $explode_vimeo[3];
						return '<iframe width="100%" height="291" src="https://player.vimeo.com/video/'.$vm_id.'" frameborder="0" allowfullscreen></iframe>';                                                                
					}else{
						return '<iframe width="100%" height="291" src="'.$Latest_Medias['image'].'" frameborder="0" allowfullscreen></iframe>';
					}                                
				}else{
					return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
				}
			}else if($Latest_Medias['type']=='video'){
				$explode_video = explode('.',$Latest_Medias['image']);			
				return '<video id="video_play_'.$post_id.'" onclick="play_video(this,'.$post_id.');" width="100%" height="291" controls><source src="'.url('/media_gallery/'.$Latest_Medias['image']).'" type="video/'.$explode_video[1].'"></video>';                                                                
			}else{			
				return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
			}	
		}else{			
			return '<img src="'.url('img/no_image.jpg/').'" alt="no_image_detail">';
		}
    }
	
	public static function get_server_query_string(){
		$get_query = @$_SERVER['QUERY_STRING'];		
		if(!empty($get_query)){
			$query = explode('&', $get_query);				
			foreach($query as $param){
				list($name, $value) = explode('=', @$param, 2);
				$params[urldecode($name)] = urldecode($value);
			}		
		}else{
			$params="";
		}
		return $params;
	}
	
	public static function save_log_details($module_type,$post_status,$post_id){		
		$modify = new \App\Models\Ei_modified();
		$modify->user_id = \Auth::user()->id;
		$modify->datetime = date('Y-m-d H:i:s');				
		$modify->module_type = $module_type;
		$modify->post_status = $post_status;
		$modify->post_id = $post_id;				
		$modify->save();
	}
	
	public static function module_type(){        
        return array('user'=>'User','subsitelist'=>'Subsites',
        	'media'=>'Media','resource'=>'Resource','post'=>'Post','theme'=>'Themes','tag'=>'Tags','dossier'=>'Dossier',
			'country'=>'Country','region_tag'=>'Region Tags','region'=>'Region','group'=>'Group','project'=>'DC Project','ilo'=>'ILO','page'=>'Pages','template'=>'Templates',
			'staff_profile'=>'Staff Profiles','executive_board'=>'Executive Board','staff_category' => 'Staff Categories','external_slide'=>'External Slider','menu'=>'Menu','newsletter_email_campaign'=>'Newsletter Email Campaign',
			'newsletter_email_campaign_archive'=>'Newsletter Email Campaign Archive','newsletter_contacts'=>'Newsletter Contacts','section'=>'Section','sub_section_insert'=>'Sub Section','uiswidget'=>'UIS widget',
			'newsletter_contact_lists'=>'Newsletter Contact Lists','newsletter_templates'=>'Newsletter Templates','language'=>'Languages','label'=>'Labels','inquiry_form'=>'Inquiry Form','inquiry_email'=>'Inquiry Email','faq'=>'Support (FAQ)','home_page_slider'=>'Home Page Slider','statistic'=>'Statistic','barometer'=>'Barometer');    }
	
	public static function section_type(){
		return array('section' =>'Section','dc_project' =>'DC Project','ei_affiliates' =>'EI Affiliates');			
	}
}
