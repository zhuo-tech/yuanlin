define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'news/index' + location.search,
                    add_url: 'news/add',
                    edit_url: 'news/edit',
                    // del_url: 'news/del',
                    multi_url: 'news/multi',
                    import_url: 'news/import',
                    table: 'news',
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
                        {field: 'id', title: __('Id'),searchable:false,},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'link', title: __('Link'), operate: 'LIKE',searchable:false,},
                        {field: 'type', title: __('Type'),searchList: {"0": "删除", "1": "正常"},},
                        {field: 'sorts', title: __('Sorts'),searchable:false,},
                        {field: 'status', title: __('Status'), searchList: {"0": "删除", "1": "正常"}, formatter: Table.api.formatter.status},
                        {field: 'create_time',visible:false,searchable:false, title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
