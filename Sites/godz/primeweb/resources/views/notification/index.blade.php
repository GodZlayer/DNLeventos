@extends('layouts.main')

@section('title')
    {{ __('Send Notification') }}
@endsection



@section('content')
    <div class="row">
        <section class="section">
            <div class="row">
                <div id="addModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form class="form-horizontal create-form" action="{{ route('notification.store') }}" id="create-form" enctype="multipart/form-data" method="POST" novalidate>
                                @csrf
                                {{-- <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">{{ __('Add New Draweritem') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div> --}}
                                <div class="modal-body">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-12 form-group mandatory">
                                                <label for="add_title" class="mandatory form-label">{{ __('Title') }}</label><span class="text-danger"></span>
                                                <input type="text" name="title"  id="title"  class="form-control" placeholder="{{ __('Title') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-12 form-group mandatory">
                                                <label for="add_message" class="form-label">{{ __('Message') }}</label><span class="text-danger"></span>
                                                <textarea type="text" id="message" name="message" class="form-control" placeholder="{{ __('Message') }}" required></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="image" class="form-label">{{ __('icon') }}</label>
                                            <div class="cs_field_img1">
                                                <div class="img_input"><i class="fas fa-upload "></i> Upload</div>
                                                <input type="file" id="image" name="image" class="image" style="display: none" accept=".jpg, .jpeg, .png, .svg">
                                                <img src="{{ asset('assets\images\Image_Preview.png') }}" alt="Preview" class="img preview-image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">

                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <p class="card-header-new-style">{{ __('Notifications') }}</p>
                            </div>
                            <div class="col-12 col-md-6 d-flex justify-content-end">
                                 <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                    + {{ __('Send Notification') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div id="toolbar">
                                    {{-- <a href="{{ route('notification.batch.delete') }}" class="btn btn-danger btn-sm btn-icon text-white" id="delete_multiple" title="Delete Notification"><em class='fa fa-trash'></em></a> --}}
                                </div>
                                <table aria-describedby="mydesc" id="table_list" data-toggle="table"
                                    data-url="{{ route('notification.show', 1) }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                    data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                                    data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false"
                                    data-escape="true"
                                    data-responsive="true" data-sort-name="id" data-sort-order="desc"
                                    data-pagination-successively-size="3" data-show-export="true" data-export-options='{"fileName": "notification-list","ignoreColumn": ["operate"]}' data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']">
                                    <thead>
                                    <tr>
                                        {{-- <th scope="col" data-field="state" data-checkbox="true"></th> --}}
                                        <th scope="col" data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                        <th scope="col" data-field="title" data-sortable="true">{{ __('Title') }}</th>
                                        <th scope="col" data-field="message" data-sortable="true">{{ __('Message') }}</th>
                                        <th scope="col" data-field="image" data-formatter="imageFormatter">{{ __('Image') }}</th>
                                        {{-- <th scope="col" data-field="send_to" data-sortable="true">{{ __('Send To') }}</th> --}}
                                        <th scope="col" data-field="operate" data-escape="false">{{ __('Action') }}</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- @section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#include_image').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#show_image').show();
                } else {
                    $('#show_image').hide();
                }
            });

            $('#include_image').trigger('change');
        });

        function userListQueryParams(params) {
            params.search = $('#search').val();
            return params;
        }

        function imageFormatter(value, row, index) {
            return value ? `<img src="${value}" alt="Image" style="max-width: 50px;">` : '';
        }
    </script>
@endsection --}}
