<style>
    @media(max-width:1280px){
    .sm-table-admin-scroll{
            overflow-x: scroll;
        }
    }
</style>
<link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
<div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Point List</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Point</a></li>
                                            <li class="breadcrumb-item active">DataTables</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <!-- <div class="card-header">
                                        
                                        <h4 class="card-title">Default Datatable</h4>
                                        <p class="card-title-desc">DataTables has most features enabled by
                                            default, so all you need to do to use it with your own tables is to call
                                        </p>
                                    </div> -->
                                    <div class="card-body">
                                        <div class="tbleSearch">
                                            <!-- <div class="entries">
                                                Shows
                                                <select class="form-select" id="" aria-label="Default select example">
                                                    <option selected>30 Entries</option>
                                                    <option value="1">10 Entries</option>
                                                    <option value="2">20 Entries</option>
                                                    <option value="3">30 Entries</option>
                                                  </select>
                                            </div> -->
                                            <div class="right">
                                                <!-- <div class="searchByDate">
                                                    Search by date : 
                                                    <input class="form-control" type="date" value="2019-08-19" id="example-date-input">
                                                    to
                                                    <input class="form-control" type="date" value="2019-09-19" id="example-date-input">
                                                </div> -->
                                                <!-- <div class="search">
                                                    <input class="form-control" type="text" placeholder="Search" id="sesrch_list" onkeyup="search();">
                                                    <i class="bx bx-search-alt"></i>
                                                </div> -->
                                                <div class="addbtn">
                                                    <!-- <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"> <i class="bx bx-plus"></i> Add Listing</button> -->
                                                    <!-- <a href="team-add"><button class="btn btn-primary" type="button"> <i class="bx bx-plus"></i> Add </button></a> -->
                                                </div> 
                                                <!-- Modal -->
                                                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Please Enter Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form class="row g-3">
                                                                <div class="col-md-12">
                                                                    <label for="inputState" class="form-label">How many days ?</label>
                                                                    <select id="inputState" class="form-select">
                                                                      <option selected>Choose...</option>
                                                                      <option>...</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-12">
                                                                    <p class="mb-2">In which page</p>
                                                                    <div class="form-check d-inline-block">
                                                                      <input class="form-check-input" type="checkbox" id="gridCheck">
                                                                      <label class="form-check-label" for="gridCheck">
                                                                        Home
                                                                      </label>
                                                                    </div>
                                                                    <div class="form-check d-inline-block ml-2">
                                                                        <input class="form-check-input" type="checkbox" id="gridCheck">
                                                                        <label class="form-check-label" for="gridCheck">
                                                                          Listing
                                                                        </label>
                                                                      </div>
                                                                  </div>

                                                                <div class="col-12">
                                                                  <label for="inputAddress" class="form-label">Which category</label>
                                                                  <select id="inputState" class="form-select">
                                                                    <option selected>Choose...</option>
                                                                    <option>...</option>
                                                                  </select>
                                                                </div>
                                                                <div class="col-12">
                                                                  <label for="inputAddress2" class="form-label">Total Reward required</label>
                                                                  <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px"></textarea>
                                                                </div>
                                                           
                                                                <div class="col-12">
                                                                  <button type="submit" class="btn btn-primary">Submit</button>
                                                                </div>
                                                              </form>
                                                        </div>

                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <input type="hidden" id="sl_no" value="">
                                        <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th> Module </th>
                                                <!-- <th>Email</th>
                                                <th>Phone</th> -->
                                                <th> Purpose</th>
                                                <th> Points </th>
                                                <!-- <th>Status</th> -->
                                                <th>Action</th>
                                            </tr>
                                            </thead>
        
        
                                            <tbody id="top_search_list">
                                                <?php
                                            if(!empty($points_list)){
                                            $x=1;
                                            foreach($points_list as $key=>$list){ 
                                            ?>
                                        <tr>
                                            <td>
                                                <?php echo $x++;?>
                                            </td>
                                            <td>
                                                <?php echo $list->module_name;?>
                                            </td> 
                                             <td>
                                             <?php echo $list->point_name;?>
                                            </td> 
                                            <?php if($list->point_type = 2){?>
                                                    <td style="color:green;  font-weight:900;">
                                                        <?php echo $list->required_point;?>
                                                    </td> 
                                            <?php }else{?>
                                                    <td style="color:red; font-weight:900;">
                                                        <?php echo $list->required_point;?>
                                                    </td> 
                                            <?php }?>

                                           
                                            
                                            
                                           
                                            <td>
                                             <!-- <a href="javascript:void(0)" class="delete_btn btn btn-sm btn-warning mt-2 mx-2" onclick='delete_btn(<?php //echo $list->id;?>)'><i class="fa fa-eye" type="button"></i></a> -->

                                            <a href="<?php echo BASEPATH; ?>console/point/edit?id=<?php echo $list->id; ?>" class='edit_btn btn btn-sm btn-success mt-2 mx-2'  ><i class="fa fa-list-alt" aria-hidden="true" type='button'></i></a>
                                            </td> 
                                           
                                        </tr>
                                    <?php   
                                    }
                                }else{
                                    ?>
                                    <tr>
                                        <td><?php echo "No Data Found.."; ?></td>
                                    </tr>
                                    <?php 
                                    } 
                                ?>

                                            
                                            </tbody>
                                        </table>
                                        <!-- <input type="hidden" id="user_type" value="<?php //if(!empty($referrel_code_list[0]->user_type)){ echo $referrel_code_list[0]->user_type;}?>">
                                        <input type="hidden" id="team_id" value="<?php //if(!empty($referrel_code_list[0]->team_member_id)){ echo $referrel_code_list[0]->team_member_id;}?>"> -->
                                        <div class="row">
                                            <div class="col-sm-12 col-md-5">
                                                <!-- <div class="dataTables_info" id="datatable-buttons_info" role="status" aria-live="polite">Showing 1 to 10 of <?php //echo $pagination->total_page;?> entries</div> -->
                                            </div>
                                            <div class="col-sm-12 col-md-7">
                                                <div class="dataTables_paginate paging_simple_numbers" id="datatable-buttons_paginate">
                                                    <ul class="pagination">
                                                        <li class="paginate_button page-item previous" id="datatable-buttons_previous"><a href="#" aria-controls="datatable-buttons" data-dt-idx="0" tabindex="0" class="page-link" onclick="pre('y');">Previous</a></li>
                                                            <?php if(!empty($pagination->total_page)){ for($i=1;$i<=$pagination->total_page;$i++){?> <li class="paginate_button page-item"><a href="#" aria-controls="datatable-buttons" data-dt-idx="1" tabindex="0" class="page-link" onclick="pagination(<?php echo $i;?>);"><input type="hidden" id="last_row_<?php echo $i;?>" value="<?php echo $pagination->last_row;?>"><?php echo $i;?></a></li><?php }}?>
                                                            <input type="hidden" id="page_no" value="0">
                                                            <input type="hidden" id="total_page" value="<?php if(!empty($pagination->total_page)){ echo $pagination->total_page;}?>">
                                                       <!-- <li class="paginate_button page-item "><a href="#" aria-controls="datatable-buttons" data-dt-idx="2" tabindex="0" class="page-link">2</a></li>
                                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatable-buttons" data-dt-idx="3" tabindex="0" class="page-link">3</a></li>
                                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatable-buttons" data-dt-idx="4" tabindex="0" class="page-link">4</a></li>
                                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatable-buttons" data-dt-idx="5" tabindex="0" class="page-link">5</a></li>
                                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatable-buttons" data-dt-idx="6" tabindex="0" class="page-link">6</a></li> -->
                                                        <li class="paginate_button page-item next" id="datatable-buttons_next"><a href="javascript:void(0);" aria-controls="datatable-buttons" data-dt-idx="7" tabindex="0" class="page-link" onclick="next('z');">Next</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
    
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js" defer></script> -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     
        // $(document).ready(function () {
        //     $('#datatable').DataTable();
        // });

        function delete_btn(id){
                //let id = $("#id").val();
                //alert(id);
            swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover it!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonColor: '#d33',
            })
            .then((willDelete) => {
                if (willDelete.value == true) {
                    // let url = "team-del";
                    // let method = "GET";
                    // let Data = {"id":id};
                    
                   
                    let APIData = {'id':id}
                    $.ajax({
                        url: "<?php echo BASEPATH ?>api/v1/team-del",
                        type: "get",
                        data: APIData,
                        success: function(response) {
                            if(response.message == "Delete Successfull"){
                                // window.location.replace("<?php //echo BASEPATH ?>console/dashboard");
                                window.location.reload();
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Sorry...',
                                    text: 'Invalid Data!'
                                })
                            }
                            
                        }
                    });
            }
            });
        }
</script>

<script>
    function search(){
        let user_id = $("#team_id").val();
        let user_type = $("#user_type").val();
        let text = $("#sesrch_list").val();
        if(text.length > 0){

        
        let APIData = {"text":text,"user_id":user_id,"user_type":user_type};
            $.ajax({
            url: "<?php echo BASEPATH ?>api/v1/referrel-code-search",
            type: "get",
            data: APIData,
            success : function(data){
              console.log(data);
              let sl = 1;
              //sl = sl+1;
              $("#top_search_list").empty();
              $.each(data.data, function(index, value) {
               
                  let htmlbody = '';
                  htmlbody += '<tr>';
                  htmlbody += '<td>'+sl+'</td>';
                  htmlbody += '<td>'+value.team_member_name+'</td>';
                //   htmlbody += '<td>'+value.team_member_email+'</td>';
                //   htmlbody += '<td>'+value.team_member_phone+'</td>';
                  htmlbody += '<td>'+value.code+'</td>';
                  //htmlbody += '<td>'+value.is_used+'</td>';
                  if(value.is_used == 0){
                  htmlbody += '<td><a href="javascript:void(0)" class= "edit_btn btn btn-sm btn-success mt-2 mx-2"><i class="fas fa-check" type="button" ></i></a></td>';
                  }else{
                    htmlbody += '<td><a href="javascript:void(0)" class= "edit_btn btn btn-sm btn-danger mt-2 mx-2"><i class="fa fa-window-close" type="button" ></i></a></td>';
                  }
                  htmlbody += '<td><a href="" class="edit_btn btn btn-sm btn-warning mt-2 mx-2"  ><i class="fa fa-list-alt" aria-hidden="true" type="button"></i></a></td>';
                  htmlbody += '</tr>';
                            
                  sl++;
                  $("#sl_no").val(sl);
                 $("#top_search_list").append(htmlbody);
              });
             
            //    $("#page_no").val(data.pagination.page_no);
            //    $("#total_page").val(data.pagination.total_page);
             
            }
          });
        }else{
            pagination(1)
        }
    }
</script>

<script>
   function pagination(key_id){
      $("#top_search_list").empty();
      let last_row = $("#last_row_"+key_id).val();
    //   let user_id = $("#team_id").val();
    //   let user_type = $("#user_type").val();
     // alert(key_id+'---'+last_row);
      // let sl = $("#sl_no").val();
   
          let APIData = {"lastrow":last_row,"page_no":key_id};
            $.ajax({
            url: "<?php echo BASEPATH ?>api/v1/point-list",
            type: "get",
            data: APIData,
            success : function(data){
              console.log(data);
              if(key_id ==0){
                 key_id = 1;
              }
              let sl = (key_id - 1)*5;
              sl = sl+1;
              $.each(data.data, function(index, value) {
               
                  let htmlbody = '';
                  htmlbody += '<tr>';
                  htmlbody += '<td>'+sl+'</td>';
                  htmlbody += '<td>'+value.module_name+'</td>';
                  htmlbody += '<td>'+value.point_name+'</td>';
                  if(value.point_type == 2){
                    htmlbody += '<td style="color:green; font-weight:900;">'+value.required_point+'</td>';
                  }else{
                    htmlbody += '<td style="color:red; font-weight:900;">'+value.required_point+'</td>';
                  }
                  
                 
                //   htmlbody += '<td><a href="" class="edit_btn btn btn-sm btn-warning mt-2 mx-2"  ><i class="fa fa-list-alt" aria-hidden="true" type="button"></i></a>';
                htmlbody += '<td>';
                //   htmlbody += '<a href="javascript:void(0)" class="delete_btn btn btn-sm btn-warning mt-2 mx-2" onclick="view_btn()"><i class="fa fa-eye" type="button"></i></a>';
                  htmlbody += '<a href="<?php echo BASEPATH; ?>console/point/edit?id='+value.id+'" class="edit_btn btn btn-sm btn-success mt-2 mx-2"  ><i class="fa fa-list-alt" aria-hidden="true" type="button"></i></a>';
                  
                  htmlbody += '</td>';
                  htmlbody += '</tr>';
                    
                // $("#team_id").val(value.user_id);
                // $("#user_type").val(value.user_type);
                 
                  sl++;
                  $("#sl_no").val(sl);
                 $("#top_search_list").append(htmlbody);
              });
              

               $("#page_no").val(data.pagination.page_no);
               $("#total_page").val(data.pagination.total_page);
             
            }
          });

   }
   /////////////////////
 
   /////////////////////
   function next(no){
      let page_no = $("#page_no").val();
      let total_page = $("#total_page").val();
      
     // alert('total'+total_page+'----'+'page no'+page_no);
      if(page_no == 0){
       // alert('page no in zero');
        page_no = 2;
      }else if(page_no == total_page){
       // alert('page no is equal to Total page');
        page_no = 1;
      }else{
       // alert('page no got its increment');
         page_no++;
      }
   
    //alert(page_no);
      pagination(page_no);
   }
 function pre(s){
    
    let page_no = $("#page_no").val();
    let total_page = $("#total_page").val();
    //alert(total_page);
    if(s == 'y'){
        if(page_no == 0){
            page_no = 1;
        }else{
            page_no--;
        }
        
    }
    if(s == 'z'){
        if(page_no == total_page){
            page_no = 1;
        }else{
            page_no++;
            
        }
        
    }
   // alert(page_no);
    pagination(page_no);
 }



</script>