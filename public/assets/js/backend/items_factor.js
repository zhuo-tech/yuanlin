define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'items_factor/index' + location.search,
                    add_url: 'items_factor/add',
                    edit_url: 'items_factor/edit',
                    // del_url: 'items_factor/del',
                    multi_url: 'items_factor/multi',
                    import_url: 'items_factor/import',
                    table: 'items_factor',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'item_id',visible:false, title: __('Item_id')},
                        {field: 'item_name', title: __('项目名称')},
                        {field: 'factor_id',visible:false, title: __('Factor_id')},
                        {field: 'factor_name', title: __('factor_name')},
                        {field: 'param', title: __('Param'), operate: 'LIKE'},
                        {field: 'result', title: __('Result'), operate: 'LIKE',sortable:true},
                        {field: 'status', title: "状态",searchList:{"0":"未输入","1":"已输入","2":"已计算"}},
                        {field: 'create_time',visible:false, title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
