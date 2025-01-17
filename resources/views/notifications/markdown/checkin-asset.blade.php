@component('mail::message')
{{ trans('mail.hello') }},

{{ trans('mail.the_following_item') }}

@if (isset($item->serial))
{{ trans('mail.serial') }}: {{ $item->serial }}
@endif
@if (isset($last_checkout))
{{ trans('mail.checkout_date') }}: {{ $last_checkout }}
@endif

@if ((isset($item->name)) && ($item->name!=''))
{{ trans('mail.asset_name') }}: {{ $item->name }}
@endif
@if (($item->name!=$item->asset_tag))
{{ trans('mail.asset_tag') }}: {{ $item->asset_tag }}
@endif
@if (isset($item->manufacturer))
{{ trans('general.manufacturer') }}: {{ $item->manufacturer->name }}
@endif
@if (isset($item->model))
{{ trans('general.asset_model') }}: {{ $item->model->name }}
@endif
@if ((isset($item->model->model_number)) && ($item->model->name!=$item->model->model_number))
{{ trans('general.model_no') }}: {{ $item->model->model_number }}
@endif
@foreach($fields as $field)
@if (($item->{ $field->db_column_name() }!='') && ($field->show_in_email) && ($field->field_encrypted=='0'))
{{ $field->name }}: {{ $item->{ $field->db_column_name() } }}
@endif
@endforeach
@if ($note)
{{ trans('mail.additional_notes') }}: {{ $note }}
@endif

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
