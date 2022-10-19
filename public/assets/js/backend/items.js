define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {


    $("#c-location").on("cp:updated", function() {
        var citypicker = $(this).data("citypicker");
        var code = citypicker.getCode("district") || citypicker.getCode("city") || citypicker.getCode("province");
        $("#code").val(code);
        console.log(code);
    });

    var Controller = {
        index: function () {

            $('.btn-add').data('area',['80%','80%']);

            $('.btn-edit').data('area',['80%','80%']);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'items/index' + location.search,
                    add_url: 'items/add',
                    edit_url: 'items/edit',
                    // del_url: 'items/del',
                    multi_url: 'items/multi',
                    import_url: 'items/import',
                    table: 'items',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),searchable:false,},
                        {field: 'user_id',visible:false,searchable:false, title: __('User_id')},
                        {field: 'item_type',visible:false,searchable:false, title: "项目分类"},
                        {field: 'name', title:"名称", operate: 'LIKE'},
                        {field: 'location', title: __('Location'), operate: 'LIKE'},
                        {field: 'areas', searchable:false,title: __('Areas'), operate: 'LIKE'},
                        {field: 'item_cate_id',  visible:false,searchable:false, title: __('Item_cate_id')},
                        {field: 'item_cate_name', title: "项目类型"},
                        {field: 'status', title: __('Status'),searchList: {"0": "删除", "1": "创建","2": "已选取指标","3": "已填写指标 ","4": "已生成报告","5": "申请案例","6": "申请成功","7":"申请失败"}},
                        {field: 'images', title: __('Images'),visible:false, searchable:false,operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'introduction',searchable:false, title: __('Introduction'), operate: 'LIKE'},
                        {field: 'specific',searchable:false, title: __('Specific'), operate: 'LIKE'},
                        {field: 'programme_design', searchable:false,title: __('Programme_design'), operate: 'LIKE'},
                        {field: 'designer', title: __('Designer'),searchable:false, operate: 'LIKE'},
                        {field: 'construction', searchable:false,title: __('Construction'), operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), visible:false,searchable:false,operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), visible:false,searchable:false,operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
