define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    "index_url": "article/category/index",
                    "add_url": "article/category/add",
                    "edit_url": "article/category/edit",
                    "del_url": "article/category/del",
                    "multi_url": "article/category/multi",
                    "table": "article_category"
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'weigh',
                sortOrder: 'asc',
                escape: false,
                dblClickToEdit: false, //是否启用双击编辑
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: 'ID'},
                        {
                            field: 'name',
                            title: __('Name'),
                            align: 'left',
                            formatter: Controller.api.formatter.name
                        },
                        {
                            field: 'weigh',
                            title: __('Weigh'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return '<input type="text" class="form-control text-center text-weigh" data-id="' + row.id + '" value="' + value + '" style="width:50px;margin:0 auto;" />';
                            }
                        },
                        {
                            field: 'id',
                            title: '<a href="javascript:;" class="btn btn-success btn-xs btn-toggle"><i class="fa fa-chevron-up"></i></a>',
                            operate: false,
                            formatter: Controller.api.formatter.subnode
                        },
                        {
                            field: 'editsub',
                            operate: false,
                            title: '添加子类',
                            table: table,
                            buttons: [],
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                var that    = $.extend({}, this);
                                var table   = $(that.table).clone(true);
                                var options = table ? table.bootstrapTable('getOptions') : {};
                                var buttons = [];
                                buttons.push({
                                    name: 'editsub',
                                    text: '添加子类',
                                    title: '添加子类',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    url: 'article/category/add?pid=' + row.id
                                });
                                this.buttons = buttons;
                                that.table   = table;
                                return Table.api.buttonlink(that, buttons, value, row, index, 'buttons');
                            },
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                pagination: false,
                search: false,
                commonSearch: false,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            //当内容渲染完成后
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                //显示隐藏子节点
                $(".btn-node-sub").off("click").on("click", function (e) {
                    var status = $(this).data("shown") ? true : false;
                    $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
                        $(this).closest("tr").toggle(!status);
                    });
                    $(this).data("shown", !status);
                    return false;
                });
            });
            //展开隐藏一级
            $(document.body).on("click", ".btn-toggle", function (e) {
                $("a.btn[data-id][data-pid][data-pid!=0].disabled").closest("tr").hide();
                var that = this;
                var show = $("i", that).hasClass("fa-chevron-down");
                $("i", that).toggleClass("fa-chevron-down", !show);
                $("i", that).toggleClass("fa-chevron-up", show);
                $("a.btn[data-id][data-pid][data-pid!=0]").not('.disabled').closest("tr").toggle(show);
                $(".btn-node-sub[data-pid=0]").data("shown", show);
            });
            //展开隐藏全部
            $(document.body).on("click", ".btn-toggle-all", function (e) {
                var that = this;
                var show = $("i", that).hasClass("fa-plus");
                $("i", that).toggleClass("fa-plus", !show);
                $("i", that).toggleClass("fa-minus", show);
                $(".btn-node-sub.disabled").closest("tr").toggle(show);
                $(".btn-node-sub").data("shown", show);
            });
            $(document).on("change", ".text-weigh", function () {
                $(this).data("params", {weigh: $(this).val()});
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            formatter: {
                name: function (value, row, index) {
                    return row.pid > 0 ? "<span class='text-muted'>" + value + "</span>" : value;
                },
                subnode: function (value, row, index) {
                    return '<a href="javascript:;" data-toggle="tooltip" title="显示分类" data-id="' + row.id + '" data-pid="' + row.pid + '" class="btn btn-xs '
                        + (row.pid == 0 ? 'btn-success' : 'btn-default disabled') + ' btn-node-sub"><i class="fa fa-sitemap"></i></a>';
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});