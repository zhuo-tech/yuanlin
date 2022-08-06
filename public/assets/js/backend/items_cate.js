define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'items_cate/index' + location.search,
                    add_url: 'items_cate/add',
                    edit_url: 'items_cate/edit',
                    del_url: 'items_cate/del',
                    multi_url: 'items_cate/multi',
                    import_url: 'items_cate/import',
                    table: 'items_cate',
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
                        {field: 'pid', title: "父类型"},
                        {field: 'name', title: __('Name'), align: 'left', formatter:function (value, row, index) {
                                return value.toString().replace(/(&|&amp;)nbsp;/g, '&nbsp;');
                            }
                        },
                        {field: 'label', title: __('Label'), operate: 'LIKE'},
                        {field: 'create_time', visible:false, title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', visible:false, title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                pagination: false,
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
