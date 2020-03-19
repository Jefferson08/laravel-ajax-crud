@extends('layouts.app')

@section('content')

<div class="row" style="margin-top: 15px;">
    <h1 style="padding-left: 15px;">Task List</h1>

    <div class="col-lg-12">
        <hr>
        <button id="btn_add_task" type="button" class="btn btn-success" data-toggle="modal" data-target="#taskModal">
            Create new task
        </button>
        <hr>

        <table class="table table-bordered" id="tasks">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Created at</th>
                    <th>Updated at</th>
                    <th>Actions</th>
                </tr>
            </thead>
        
            <tbody>
               
            </tbody>
            
        </table>
    </div>
</div>

  <!-- Modal -->
  <div class="modal fade" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="taskModalTitle">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form id="task_form" method="POST">
                <div class="alert alert-danger d-none" id="message_box">
                </div>
                <div class="form-group">
                  <label for="task_title">Title</label>
                  <input type="email" name="task_title" class="form-control" id="task_title" placeholder="Enter title...">
                  <span class="text-danger"></span>
                </div>
                <div class="form-group">
                    <label for="task_body">Body</label>
                    <textarea class="form-control" id="task_body" name="task_body" rows="3" placeholder="Enter body..."></textarea>
                    <span class="text-danger"></span>
                  </div>
              </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" id="btn_save_task" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>

@section('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
@endsection


<script>
    $(document).ready( function () {
        var table = $('#tasks').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('tasks.index') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'body', name: 'body' },
                { data: 'created_at', name: 'created_at', width: '18%'},
                { data: 'updated_at', name: 'updated_at', width: '18%'},
                { data: 'action', name: 'actions', orderable: false, searchable: false, width: '18%'},
            ]
        }); 

        $('#taskModal').on('hidden.bs.modal',function(){
          $('#btn_save_task').unbind('click');
          $('#task_form').unbind('submit');
        });

        function cleanErrors(){
            $('#message_box').addClass('d-none');
            $('#task_title').removeClass('border border-danger');
            $('#task_title').next().html('');
            $('#task_body').removeClass('border border-danger');
            $('#task_body').next().html('');
        }

        function showErrors(message, errors){
          $('#message_box').removeClass('d-none').html(message);
          $.each(errors, function(key, value){
            $('#'+key).addClass('border border-danger');
            $('#'+key).next().html(value);
          });
        }

        $('#btn_add_task').on('click', function() {
          $('#taskModalTitle').html('Create new Task');
          $('button#btn_save_task').html('Create');
          $('input#task_title').val('');
          $('textarea#task_body').val('');

          cleanErrors();

          $('#task_form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
              type: 'POST',
              url: "{{ route('tasks.store')}}",
              data: $(this).serialize(),
              success:function(response){
                if(response.success === false){
                    cleanErrors();
                    showErrors(response.message, response.errors);
                } else {
                  $('#taskModal').modal('hide');
                  Swal.fire(
                    'Success!',
                    response.message,
                    'success'
                  )
                  table.draw(); //Drawing table again
                }
              },
              error:function(error) {
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Something went wrong!',
                  footer: error.status
                })
              }
            });
          });

          $('#btn_save_task').on('click', function() {
            $('#task_form').submit();
          });
        });

        //Edit task

        $('#tasks tbody').on( 'click', '#btn_edit_task', function () {

            cleanErrors();

            var data = table.row( $(this).parents('tr') ).data();

            var id = data['id'];

            $('#taskModalTitle').html("Edit Task - " + id);
            $('button#btn_save_task').html('Save Changes');

            var get_url = "{{ route('tasks.show', ':id')}}";
            get_url = get_url.replace(':id', id);

            $.ajax({
                type: 'GET',
                url: get_url,
                success:function(response){
                    $('input#task_title').val(response.title);
                    $('textarea#task_body').val(response.body);
                },
                error:function(error){
                  Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!',
                    footer: error.status
                  })
                }
            });

            $('#task_form').submit(function(e){
              e.preventDefault();

              var put_url = "{{ route('tasks.update', ':id')}}";
              put_url = put_url.replace(':id', id);
              
              $.ajax({
                type: 'PUT',
                url: put_url,
                data: $(this).serialize(),
                success:function(response){
                  if(response.success === false){
                    cleanErrors();
                    showErrors(response.message, response.errors);
                  } else {
                    $('#taskModal').modal('hide');
                    Swal.fire(
                      'Success!',
                      response.message,
                      'success'
                    )
                    table.draw(); //Drawing table again
                  }
                },
                error:function(error){
                  Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!',
                    footer: error.status
                  })
                }
              });
            });

            $('#btn_save_task').on('click', function(){
                $('#task_form').submit();
            })
        });

        //Delete task

        $('#tasks tbody').on( 'click', '#btn_delete_task', function (){
          var data = table.row( $(this).parents('tr') ).data();

          var id = data['id'];

          Swal.fire({
            title: 'Delete task '+id+'?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
            if (result.value) {
              
              var delete_url = "{{ route('tasks.destroy', ':id')}}";
              delete_url = delete_url.replace(':id', id);

              $.ajax({
                type: 'DELETE',
                url: delete_url,
                success:function(response){
                  if(response.success === true){
                    Swal.fire(
                      'Success!',
                      response.message,
                      'success'
                    )

                    table.draw();
                  }
                },
                error:function(error){
                  Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!',
                    footer: error.status
                  })
                }
              });
              
              
            }
          })
        });
    });

</script>

@endsection