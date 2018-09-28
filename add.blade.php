{{--*/ $active='add_user' /*--}}
@extends('admin.layout')
@section('content')	
<script src="{{ asset('/js/custom_js/users.js') }}"></script>
<script type='text/javascript'>	
	$(document).ready(function() {
		$('.showdiv').hide();		
		$('#generatepswd').click(function() {
			 $('.showdiv').show();
			 $('#generatepswd').hide();
		});		
		$('#cancelbtn').click(function() {
			 $('.showdiv').hide();
			 $('#generatepswd').show();
		});				
		if($('#UserRoleId').val() == 5){
			//document.getElementById('uname').style.display = "none";
			document.getElementById('upswd').style.display = "none";
			document.getElementById('uactive').style.display = "none";
		}else{
			//document.getElementById('uname').style.display = "block";
			document.getElementById('upswd').style.display = "block";
			document.getElementById('uactive').style.display = "block";
		}		
	});	
	function show_ex_author_div(elem){	
		if(elem.value == 5){
			//document.getElementById('uname').style.display = "none";
			document.getElementById('upswd').style.display = "none";
			document.getElementById('uactive').style.display = "none";
		}else{
			//document.getElementById('uname').style.display = "block";
			document.getElementById('upswd').style.display = "block";
			document.getElementById('uactive').style.display = "block";
		}
	}	
	function CkCreateReplace(elementid,langid)
	{			
		if (langid=='ar') {				
			CKEDITOR.replace(elementid, {
				height : 200,
				fullPage : false,
				contentsLangDirection : 'rtl'                                                   
			});
		}else{
			CKEDITOR.replace(elementid, {
				height : 200,
				fullPage : false,
				contentsLangDirection : 'ltr'                                                   
			});    	
		}			
	}	
</script>		
<div class="row">
	<div class="col-lg-12">
		<section class="wrap">
			<header class="panel-heading">Add New User</header>
			<p>Create a brand new user and add them to this site.</p>
			<div class="userspage m15">
                <form class="form-horizontal" role="form" method="POST" action="store" enctype="multipart/form-data" id="UserAddForm">
                	<div class="form-group required" id="uname">
                		<label class="col-lg-2 control-label">Username</label>
                		<div class="col-lg-4">
							<input type="text" class="form-control" name="username" value="{{ old('username') }}" id="Userusername">
							<div class="alert-danger" id="username">{{ $errors->first('username') }}</div>                                
                        </div>
                    </div>					
					<div class="form-group required" id="upswd">
                        <label class="col-lg-2 control-label">Password</label>
                        <div class="col-lg-4">
							<input type="button" value="Show Password" id="generatepswd" onclick="document.getElementById('Userpassword').value = '{{ generatePassword() }}'" class="button" />
							<?php // Generating Password
								function generatePassword ()
								{
									$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
									$password = substr( str_shuffle( $chars ), 0, 8 );
									return $password;
								}									
							?>
							<div class="showdiv" style="display:none;">
								<input type="text" class="form-control" name="password" id="Userpassword" value="{{ old('password') }}">								
								<div class='error_field' id='password_error_msg'> </div>																																	
								<input type="button" class="button" value="Hide" id="showhide" onclick="showhide_pswd('Userpassword')"/>
								<input type="button" class="button" value="Cancel" id="cancelbtn"/>
							</div>
							<div class="alert-danger" id="password">{{ $errors->first('password') }}</div>
                        </div>																													
                    </div>                    
					<div class="form-group required" id="uemail">
                        <label class="col-lg-2 control-label">Email</label>
                        <div class="col-lg-4">
                            <input type="text" class="form-control" name="email" value="{{ old('email') }}" id="Useremail">
							<div class="alert-danger" id="email">{{ $errors->first('email') }}</div>								
                        </div>
                    </div>
                    <div class="form-group required" id="ufname">
                        <label class="col-lg-2 control-label">First Name</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" id="UserFirstname">
							<div class="alert-danger" id="firstname">{{ $errors->first('firstname') }}</div>								
                        </div>
                    </div>						
					<div class="form-group" id="ulname">
                        <label class="col-lg-2 control-label">Last Name</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" id="UserLastname">                                
                        </div>
                    </div>                       
					<div class="form-group" id="utel">
                        <label class="col-lg-2 control-label">Telephone</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="telephone" value="{{ old('telephone') }}" id="UserTelephone">
							<span class="alert-danger">{{ $errors->first('telephone') }}</span>
                        </div>
                    </div>
                    <div class="form-group" id="uactive">
                        <label class="col-lg-2 col-xs-4 control-label">Active</label>
                        <div class="col-lg-4 col-xs-4">
                        	<div class="checkbox">
                        		<label><input type="checkbox" name="is_active" value="1" id="Userisactive" checked="checked"></label>
                        	</div>                                
                        </div>
                    </div>                    
					<div class="form-group required" id="urole">
						<label class="col-lg-2 control-label">Role</label>
						<div class="col-lg-4">			
							<select class="form-control" name="user_role_id" id="UserRoleId" onchange="show_ex_author_div(this)">
								<option value="">Select Role</option>
								@foreach($fetch_roles as $role)
									<option value="{{ $role->id }}" <?php if(old('user_role_id') == $role->id) { ?> selected="selected" <?php } ?>> {{ ucfirst($role->name) }}</option>
								@endforeach
							</select>
							<div class="alert-danger" id="role">{{ $errors->first('role') }}</div>								
						</div>
					</div>						
					<div class="form-group" id="uaddress">
                        <label class="col-lg-2 control-label">Address</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="address" value="{{ old('address') }}" id="UserAddress">                                
                        </div>
                    </div>						
					<div class="form-group" id="ucity">
                        <label class="col-lg-2 control-label">City</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="city" value="{{ old('city') }}" id="UserCity">                                
                        </div>
                    </div>					
					<div class="form-group" id="ustate">
                        <label class="col-lg-2 control-label">State</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="state" value="{{ old('state') }}" id="UserState">                                
                        </div>
                    </div>						
					<div class="form-group" id="ucountry">
						<label class="col-lg-2 control-label">Country</label>
						<div class="col-lg-4">
							<select class="form-control" name="country_id" id="UserCountryId">
								<option value="">Select Country</option>
								@foreach($countryArr as $c)
									<option value="{{ $c->country_id }}" <?php if(old('country_id') == $c->country_id) { ?> selected="selected" <?php } ?>> {{ ucfirst($c->short_name) }}</option>
								@endforeach									
							</select>								
						</div>
					</div>						
					<div class="form-group" id="ucode">
                        <label class="col-lg-2 control-label">Postcode</label>
                        <div class="col-lg-4">                            
                            <input type="text" class="form-control" name="postcode" value="{{ old('postcode') }}" id="UserPostcode">
							<span class="alert-danger">{{ $errors->first('postcode') }}</span>
                        </div>
                    </div>						
					<div class="form-group" id="upic">
						<label class="col-lg-2 control-label">Profile Picture</label>
						<div class="col-lg-4">														
							<div class="ChangeProfileImage" id="OpenImgUpload">
								<img src="{{ url("/img/no_image.png") }}" id="blah" />
								<div class="ChangeProfileText">Upload Profile Picture</div>
							</div>										
							<label>(must be 250 x 250)</label>										
							<input type="hidden" name="profileDataVal" id="profileDataVal" class="hidden-image-data" />
						</div>								
					</div>					
					<div class="form-group" id="uafftxt">
						<label class="col-lg-2 control-label">Affliation Text</label>
						<div class="col-lg-4">
							<select class="selectboxafftxt form-control" size="5" id="Affliation_text" name="affliation_text[]" multiple="multiple">								
								@foreach($members as $member)
									<option value="{{ $member->ID }}" <?php if(old('affliation_text')){if(in_array($member->ID,old('affliation_text'))) { ?> selected="selected" <?php }} ?>> {{ $member->Name }}</option>																				
                            	@endforeach	                                
                            </select>														
						</div>
					</div>
						
					<div class="form-group" id="ulinks">
					<label class="col-lg-2 control-label">Links</label>
						<div class="col-lg-2">  
						<table id="website_div">
							<?php																
								if(!empty(old('website')))
								{
									$arr1=old('website');
									foreach($arr1 as $key1 => $value1) {
										if(trim(old('website')[$key1])=="") {
											unset(old('website')[$key1]);
										}
									}										
								}
								$website_count = 0;									
								if(!empty(old('website')))
								{	
									$website_count = count(old('website'));									
									for($j=0;$j< $website_count ;$j++)
									{ ?>
										<input type="hidden" id="Website_Count" name="website_count" value="{{ $website_count }}">
										<tr id="website{{$j}}">
											<td>
											<div class="col-lg-15">                            
												<input type="text" class="form-control" placeholder="Website" id="Website{{$j}}" name="website[]" value="{{ old('website')[$j] }}">								
											</div>
											</td>
										</tr>																			
									<?php
									}
								}else{ ?>
									<input type="hidden" id="Website_Count" name="website_count" value="1"/>
									<tr id="website0">
										<td>
										<div class="col-lg-15">                            
											<input type="text" class="form-control" placeholder="Website" id="Website0" name="website[]">								
										</div>
										</td>
									</tr>
								<?php }	?>
							<div class="alert-danger" id="Website_URL"></div>
						</table>							
						</div>
						<div class="col-lg-1">  
						<table>
							<tr>
								<td>
								<div class="form-group pull-left col-lg-1">									
									<div id="add_website_div">
										<?php if($website_count==0){ $variable1=0; }else{ $variable1=$website_count; } ?>
										<a class="btn btn-white btn-file btn-sm btn-sm" href="javascript:void(0);" onclick="add_user_website_div('{{ url('') }}','website_div','{{ $variable1 }}');"><i class="fa fa-plus-circle"></i> New</a>
									</div>									
								</div>
								</td>
							</tr>
						</table>
						</div>							
						<div class="col-lg-2">  
						<table id="social_media_div">
							<?php																
								if(!empty(old('social_media')))
								{
									$arr2=old('social_media');
									foreach($arr2 as $key2 => $value2) {
										if(trim(old('social_media')[$key2])=="") {
											unset(old('social_media')[$key2]);
										}
									}										
								}
								$social_media_count = 0;							
								if(!empty(old('social_media')))
								{	
									$social_media_count = count(old('social_media'));										
									for($k=0;$k<$social_media_count;$k++)
									{ ?>
										<input type="hidden" id="Social_Media_Count" name="social_media_count" value="{{ $social_media_count }}">
										<tr id="social_media{{$k}}">
											<td>
											<div class="col-lg-15">                            
												<input type="text" class="form-control" placeholder="Social Media" id="Social_media{{$k}}" name="social_media[]" value="{{ old('social_media')[$k] }}">								
											</div>
											</td>
										</tr>																			
									<?php
									}
								}
								else
								{
								?>
									<input type="hidden" id="Social_Media_Count" name="social_media_count" value="1"/>
									<tr id="social_media0">
										<td>
										<div class="col-lg-15">                            
											<input type="text" class="form-control" placeholder="Social Media" id="Social_media0" name="social_media[]">								
										</div>
										</td>
									</tr>
								<?php }	?>
							<div class="alert-danger" id="Social_Media_URL"></div>
						</table>							
						</div>
						<div class="col-lg-1">  
						<table>
							<tr>
								<td>
								<div class="form-group pull-left col-lg-1">									
									<div id="add_social_media_div">
										<?php if($social_media_count==0){ $variable2=0; }else{ $variable2=$social_media_count; } ?>
										<a class="btn btn-white btn-file btn-sm btn-sm" href="javascript:void(0);" onclick="add_user_social_media_div('{{ url('') }}','social_media_div','{{ $variable2 }}');"><i class="fa fa-plus-circle"></i> New</a>
									</div>									
								</div>
								</td>
							</tr>
						</table>
						</div>
					</div>						
					{{-- */ $tab=1; $tabp=1; /* --}}
					<div class="stepy_new m-bot15">
                        <ul id="ul-menu-list" class="stepy-titles clearfix">
                            @foreach ($langArr as $key=>$value)
                                <li id="li-tabs-{{ $tab }}" class="">
                                    <a onclick="langclick('{{ $tab }}',this,'{{ $value['name'] }}',{{ $key }})" href="javascript:void(0);">{{ $value['name'] }}</a>
                                </li>
							{{-- */ $tab++; /*--}}
                            @endforeach
                        </ul>                                                                    
                    </div>
					<input type="hidden" value="{{ $langArr[0]['name'] }}" class="CurrLang" id="CurrLang" name="CurrLang">						
					@foreach($langArr as $key=>$value)
						<div id="tabs-{{$tabp}}" class="tab_error">
							<div class="form-group">
								<label class="col-lg-2 control-label">{{ $value['name'] }} Author Name</label>
								<div class="col-lg-8">
									@if($value['id']==6)
										{{-- */ $dir = "rtl"; /* --}} 											
									@else
										{{-- */ $dir = "ltr"; /* --}} 											
									@endif
									<input type="text" dir="{{ $dir }}" class="form-control" id="Authorname{{ $value['alias_name'] }}" name="authorname_{{ $value['alias_name'] }}" value="{{ old('authorname_'.$value['alias_name']) }}">
									<div class="alert-danger" id="authorname_{{ $value['alias_name'] }}"></div>
								</div>
							</div>                    
																
							<div class="form-group">
								<label class="col-lg-2 control-label">{{ $value['name'] }} Author Bio</label>
								<div class="col-lg-8">                
									<div class="Editor">                                              
										<textarea class="form-control" id="Authorbios_{{ $value['alias_name'] }}" name="authorbios_{{ $value['alias_name'] }}" rows="6">{{ old('authorbios_'.$value['alias_name']) }}</textarea>
										<script type="text/javascript">
											CkCreateReplace("Authorbios_{{ $value['alias_name'] }}","{{ $value['alias_name'] }}");
										</script>	
									</div>									
								</div>
							</div>
						</div>
					{{-- */ $tabp++; /*--}}
					@endforeach
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="submit" class="btn btn_popup">Add New User</button>
						</div>
					</div>
						
				</form>
				<div class="clearfix"></div>					
			</div>
		</section>
	</div>
</div>		
<!-- Modal -->
<div class="modal fade" id="changeProfilePopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="position: absolute;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Upload Profile Picture</h4>
            </div>
            <div class="modal-body popup_scroll">								
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <div class="preview-wrapper">
                    <div class="image-editor" id="profileImageEditor">
                        <input type="file" id="imgupload" name="image" class="cropit-image-input" style="display:none;">
                        <div class="cropit-image-preview-container">
                            <div class="cropit-image-preview"></div>
                        </div>
                        <div class="image-size-label">Resize image</div>
                        <input type="range" class="cropit-image-zoom-input">
                        <button class="btn btn_popup" data-dismiss="modal" id="SubmitImgUpload">Submit</button>																												
                    </div>
                </div>											
            </div>
        </div>
    </div>
</div>
@endsection
