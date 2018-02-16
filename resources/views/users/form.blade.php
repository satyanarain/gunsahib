<div class="form-group">
        {!! Form::label('name', Lang::get('user.headers.name'), ['class' => 'control-label required']) !!}
        {!! Form::text('name', null, ['class' => 'form-control','required' => 'required']) !!}
</div>
@if($user->user_name!='')
<div class="form-group">
        {!! Form::label('user_name', Lang::get('User Name'), ['class' => 'control-label required']) !!}
        {!! Form::text('user_name', null, ['class' => 'form-control','readonly'=>'readonly']) !!}
</div>
@else
<div class="form-group">
        {!! Form::label('user_name', Lang::get('Username'), ['class' => 'control-label required']) !!}
        {!! Form::text('user_name', null, ['class' => 'form-control','required' => 'required']) !!}
</div>
@endif
<div class="form-group">
{!! Form::label('email', Lang::get('user.headers.email'), ['class' => 'control-label required']) !!}
    {!! Form::email('email', null, ['class' => 'form-control','required' => 'required']) !!}
</div>
<div class="form-group">
    {!! Form::label('address', Lang::get('user.headers.address'), ['class' => 'control-label']) !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    @php $countries=displayList('countries','country_name');@endphp
     {!! Form::label('country', Lang::get('user.headers.country'), ['class' => 'control-label required']) !!}
     {!! Form::select('country', $countries,isset($user->country) ? $user->country : selected,
    ['class' => 'form-control', 'placeholder'=>'Select Country','required' => 'required']) !!}
</div>
<div class="form-group">
    {!! Form::label('city', Lang::get('user.headers.city'), ['class' => 'control-label']) !!}
    {!! Form::text('city',  null, ['class' => 'form-control']) !!}
</div> 
<div class="form-group">
  {!! Form::label('mobile', Lang::get('user.headers.mobile'), ['class' => 'control-label required']) !!}
    {!! Form::text('mobile', null, ['class' => 'form-control','required' => 'required','maxlength'=>10,'pattern'=>"[0-9]{10}"]) !!}
</div>
 @php 
 if($user->date_of_birth!='')
 {
 $date_of_birth = date('d-m-Y', strtotime($user->date_of_birth));
 }
 @endphp
<div class="form-group">
    {!! Form::label('date_of_birth', Lang::get('user.headers.date_of_birth'), ['class' => 'control-label']) !!}

    <div class="input-group date">
        <div class="input-group-addon">
            <i class="fa fa-calendar"></i>
        </div>
        {!! Form::text('date_of_birth', $date_of_birth, ['class' => 'multiple_date','readonly'=>'readonly']) !!}
      </div>
    <!-- /.input group -->
</div>
 @if($user->image_path!='')
<div class="form-group">
@if($user->image_path!='')
{!! Form::label('image_path', Lang::get('Existing Image'), ['class' => 'control-label']) !!}
{{Html::image('images/photo/'.$user->image_path, 'a picture', array('width' => '100','height'=>'100'))}}
@else
{!! Form::label('image_path', Lang::get('Existing Image'), ['class' => 'control-label']) !!}
{{Html::image('images/photo/noimage.png', 'a picture', array('width' => '100','height'=>'100'))}}
@endif
</div>
@endif
<div class="form-group">
    {!! Form::label('image_path', Lang::get('user.headers.image_path'), ['class' => 'control-label']) !!}
    {!! Form::file('image_path',['class' => 'form-control','onchange'=>'loadFile(event)']) !!}
</div>
<img id="output" width="100" height="100"/>

 {!! Form::submit(Lang::get('common.titles.save'), ['class' => 'btn btn-success']) !!}
 <script>

 $('#image_path').change(function () {
  var ext = $('#image_path').val().split('.').pop().toLowerCase();
 if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
    $("#output").hide();
    alert('Only JPG, JPEG, PNG &amp; GIF files are allowed.' );
    return false;
    
}

});
 var loadFile = function(event) {
       $("#output").show();
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
  };  
 
     function validateForm(){
      var usernane=   $("#user_name").val();
     nospace = usernane.split(' '); //we split the string in an array of strings using     whitespace as separator
     
     if(nospace.length>1)
     {
         alert("Space is not allowed in user name");
           return false; 
     }
     
     var ext = $('#image_path').val().split('.').pop().toLowerCase();
     if(ext!='')
     {
      if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
       $("#output").hide();
       alert('invalid extension!');
       return false;

       }
     }
 }  
</script>