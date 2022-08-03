define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
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
                        {field: 'id', title: __('Id')},
                        {field: 'factor_id', title: __('Factor_id')},
                        {field: 'max', title: __('Max'), operate: 'LIKE'},
                        {field: 'min', title: __('Min'), operate: 'LIKE'},
                        {field: 'national_stand', title: __('National_stand'), operate: 'LIKE'},
                        {field: 'input_mode', title: __('Input_mode'), operate: 'LIKE'},
                        {field: 'method', title: __('Method'), operate: 'LIKE'},
                        {field: 'source', title: __('Source'), operate: 'LIKE'},
                        {field: 'link', title: __('Link'), operate: 'LIKE'},
                        {field: 'status', title: __('Status')},
                        {field: 'create_time', visible:false, title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', visible:false, title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'create_by',visible:false,  title: __('Create_by')},
                        {field: 'update_by',visible:false,  title: __('Update_by')},
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
