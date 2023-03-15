<!-- Serial -->
<div class="form-group {{ $errors->has('serial') ? ' has-error' : '' }}">
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ trans('admin/hardware/form.serial') }} </label>
    <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'serial')) ? ' required' : '' }}">
        <input class="form-control" type="text" name="{{ $fieldname }}" id="{{ $fieldname }}" value="{{ old('serial', $item->serial) }}" />
        {!! $errors->first('serial', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
	@if ($snipeSettings->auto_increment_assets == '2')
        <div class="col-md-2 col-sm-12">
            <button class="add_field_button btn btn-default btn-sm">
                <i class="fas fa-plus"></i>
            </button>
        </div>
	@endif
</div>
