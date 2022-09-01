define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $('.btn-add').data('area',['80%','80%']);

            $('.btn-edit').data('area',['80%','80%']);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'factor_detail/index' + location.search,
                    add_url: 'factor_detail/add',
                    edit_url: 'factor_detail/edit',
                    del_url: 'factor_detail/del',
                    multi_url: 'factor_detail/multi',
                    import_url: 'factor_detail/import',
                    table: 'factor_detail',
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
                        {field: 'id', title: __('Id'),searchable:false},
                        {field: 'factor_id',visible:false, title: __('Factor_id'),searchable:false},
                        {field: 'factor_name', title: __('指标')},

                        {field: 'max', title: __('Max'), operate: 'LIKE',searchable:false},
                        {field: 'min', title: __('Min'), operate: 'LIKE',searchable:false},
                        {field: 'national_stand', title: __('National_stand'), operate: 'LIKE',searchable:false},
                        {field: 'input_mode', title: "网页输入模式" ,operate: 'LIKE',searchList: {"A": "根据公式", "C": "问卷模式","D": "直接输入结果"}},
                        {field: 'method', title: __('Method'),visible:false,searchable:false ,operate: 'LIKE'},
                        {field: 'source', title: __('Source'),visible:false, searchable:false,operate: 'LIKE'},
                        {field: 'link', title: __('Link'),visible:false, searchable:false,operate: 'LIKE'},
                        {field: 'status', title: __('Status'),searchList: {"0": "删除", "1": "正常"}},
                        {field: 'create_time', visible:false,searchable:false, title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', visible:false,searchable:false, title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'create_by',visible:false, searchable:false, title: __('Create_by')},
                        {field: 'update_by',visible:false, searchable:false, title: __('Update_by')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('post-body.bs.table',function(){
                $(".btn-editone").data("area",["80%","80%"]);
            })

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
