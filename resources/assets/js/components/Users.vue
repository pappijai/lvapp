<template>
    <div class="container">
        <div class="row mt-5">
          <div class="col-md-12 col-md-12 col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users Table</h3>

                <div class="card-tools">
                    <button class="btn btn-success" @click="newModal">Add New <span class="fas fa-user-plus fa-fw"></span></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <tbody><tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Registered At</th>
                        <th>Modify</th>
                    </tr>
                    <div hidden>{{id = 1}}</div>
                    <tr div v-for="user in users" :key="user.id">
                        <td>{{id++}}</td>
                        <td>{{user.name}}</td>
                        <td>{{user.email}}</td>
                        <td>{{user.type | upText}}</td>
                        <td>{{user.created_at | myDate}}</td>
                        <td>
                            <a href="#" @click="editModal(user)">
                                <i class="fas fa-edit text-blue"></i>    
                            </a>
                            / 
                            <a href="#" @click="deleteUser(user.id)">
                                <i class="fas fa-trash text-red"></i>    
                            </a> 
                        </td>
                    </tr>
                </tbody></table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
        <div class="modal fade" id="addusermodal" tabindex="-1" role="dialog" aria-labelledby="addusermodalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addusermodalLabel">{{editmode ? 'Update User':'Add New User'}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editmode ? updateUser() : createUser()">
                    <div class="modal-body">
                        
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name" placeholder="Name"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                            <has-error :form="form" field="name"></has-error>
                        </div>

                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email" placeholder="Email Address"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                            <has-error :form="form" field="email"></has-error>
                        </div>
                        
                        <div class="form-group">
                            <textarea v-model="form.bio" type="text" name="bio" placeholder="Bio"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }">
                            </textarea>
                            <has-error :form="form" field="bio"></has-error>
                        </div>

                        <div class="form-group">
                            <select v-model="form.type" name="type"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                <option value="">Select User Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">Standard User</option>
                                <option value="author">Author</option>
                            </select>
                            <has-error :form="form" field="type"></has-error>
                        </div>

                        <div class="form-group">
                            <input v-model="form.password" type="password" name="password" placeholder="Password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                            <has-error :form="form" field="password"></has-error>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
                            <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </div> 
                    </form>               
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return{
                users: {},
                editmode: false,
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                })
            }
        },
        methods: {
            loadUsers(){
                axios.get('api/user').then(({ data }) => (this.users = data));
            },
            createUser(){
                this.$Progress.start()
                this.form.post('api/user')
                .then(() => {
                    Fire.$emit('AfterCreate');
                    $('#addusermodal').modal('hide');
                    toast({
                        type: 'success',
                        title: 'User Created successfully'
                    })
                    this.$Progress.finish()
                })
                .catch(() => {

                })
            },
            deleteUser(id){
                swal({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        // Send ajax request to server
                        if(result.value){
                            this.form.delete('api/user/'+id).then(() => {
                                swal(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                Fire.$emit('AfterDelete');
                                
                            }).catch(() =>{
                                swal("Failed", "There was something wrong.","Warning");
                            })
                        }
                    })                
            },
            newModal(){
                this.editmode = false;
                this.form.reset();
                $('#addusermodal').modal('show');
            },
            editModal(user){
                this.editmode = true;
                this.form.reset();
                $('#addusermodal').modal('show');
                this.form.fill(user);
            },
            updateUser(){
                this.$Progress.start();
                this.form.put('api/user/'+ this.form.id)
                .then(() => {
                    Fire.$emit('AfterUpdate');
                    $('#addusermodal').modal('hide');
                    swal(
                        'Updated!',
                        'Information has been updated.',
                        'success'
                    )
                    this.$progress.success();
                })
                .catch(() =>{
                    this.$Progress.fail();
                });
            }
        },
        created() {
            this.loadUsers();
            Fire.$on('AfterCreate',() => {
                this.loadUsers()
            })
            Fire.$on('AfterDelete',() => {
                this.loadUsers()
            })
            Fire.$on('AfterUpdate',() => {
                this.loadUsers()
            })
            //setInterval(() => this.loadUsers(), 3000);

        }
    }
</script>
