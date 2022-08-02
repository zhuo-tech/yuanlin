define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'items/index' + location.search,
                    add_url: 'items/add',
                    edit_url: 'items/edit',
                    del_url: 'items/del',
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
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'item_type', title: __('Item_type')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'location', title: __('Location'), operate: 'LIKE'},
                        {field: 'areas', title: __('Areas'), operate: 'LIKE'},
                        {field: 'item_cate_id', title: __('Item_cate_id')},
                        {field: 'images', title: __('Images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'introduction', title: __('Introduction'), operate: 'LIKE'},
                        {field: 'specific', title: __('Specific'), operate: 'LIKE'},
                        {field: 'programme_design', title: __('Programme_design'), operate: 'LIKE'},
                        {field: 'designer', title: __('Designer'), operate: 'LIKE'},
                        {field: 'construction', title: __('Construction'), operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
