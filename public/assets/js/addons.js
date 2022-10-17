define([], function () {
    require.config({
    paths: {
        'simditor': '../addons/simditor/js/simditor.min',
    },
    shim: {
        'simditor': [
            'css!../addons/simditor/css/simditor.min.css',
        ]
    }
});
require(['form'], function (Form) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        if ($(Config.simditor.classname || '.editor', form).size() > 0) {
            //修改上传的接口调用
            require(['upload', 'simditor'], function (Upload, Simditor) {
                var editor, mobileToolbar, toolbar;
                Simditor.locale = 'zh-CN';
                Simditor.list = {};
                toolbar = ['title', 'bold', 'italic', 'underline', 'strikethrough', 'fontScale', 'color', '|', 'ol', 'ul', 'blockquote', 'code', 'table', '|', 'link', 'image', 'hr', '|', 'indent', 'outdent', 'alignment'];
                mobileToolbar = ["bold", "underline", "strikethrough", "color", "ul", "ol"];
                $(Config.simditor.classname || '.editor', form).each(function () {
                    var id = $(this).attr("id");
                    editor = new Simditor({
                        textarea: this,
                        toolbarFloat: false,
                        toolbar: toolbar,
                        pasteImage: true,
                        defaultImage: Config.__CDN__ + '/assets/addons/simditor/images/image.png',
                        upload: {url: '/'},
                        allowedTags: ['div', 'br', 'span', 'a', 'img', 'b', 'strong', 'i', 'strike', 'u', 'font', 'p', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'h1', 'h2', 'h3', 'h4', 'hr'],
                        allowedAttributes: {
                            div: ['data-tpl', 'data-source', 'data-id'],
                            span: ['data-id']
                        },
                        allowedStyles: {
                            div: ['width', 'height', 'padding', 'background', 'color', 'display', 'justify-content', 'border', 'box-sizing', 'max-width', 'min-width', 'position', 'margin-left', 'bottom', 'left', 'margin', 'float'],
                            p: ['margin', 'color', 'height', 'line-height', 'position', 'width', 'border', 'bottom', 'float'],
                            span: ['text-decoration', 'color', 'margin-left', 'float', 'background', 'padding', 'margin-right', 'border-radius', 'font-size', 'border', 'float'],
                            img: ['vertical-align', 'width', 'height', 'object-fit', 'float', 'margin', 'float'],
                            a: ['text-decoration']
                        }
                    });
                    editor.uploader.on('beforeupload', function (e, file) {
                        Upload.api.send(file.obj, function (data) {
                            var url = Fast.api.cdnurl(data.url);
                            editor.uploader.trigger("uploadsuccess", [file, {success: true, file_path: url}]);
                        });
                        return false;
                    });
                    editor.on("blur", function () {
                        this.textarea.trigger("blur");
                    });
                    Simditor.list[id] = editor;
                });
            });
        }
    }
});

require.config({
    paths: {
        'summernote': '../addons/summernote/lang/summernote-zh-CN.min'
    },
    shim: {
        'summernote': ['../addons/summernote/js/summernote.min', 'css!../addons/summernote/css/summernote.css'],
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定summernote事件
            if ($(Config.summernote.classname || '.editor', form).size() > 0) {
                var selectUrl = typeof Config !== 'undefined' && Config.modulename === 'index' ? 'user/attachment' : 'general/attachment/select';
                require(['summernote'], function () {
                    var imageButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file-image-o"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=image/", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            context.invoke('editor.insertImage', url);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };
                    var attachmentButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=*", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            var node = $("<a href='" + url + "'>" + url + "</a>");
                                            context.invoke('insertNode', node[0]);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };
                    $(Config.summernote.classname || '.editor', form).each(function () {
                        $(this).summernote($.extend(true, {}, {
                            height: 250,
                            lang: 'zh-CN',
                            fontNames: [
                                'Arial', 'Arial Black', 'Serif', 'Sans', 'Courier',
                                'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande',
                                "Open Sans", "Hiragino Sans GB", "Microsoft YaHei",
                                '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆',
                            ],
                            fontNamesIgnoreCheck: [
                                "Open Sans", "Microsoft YaHei",
                                '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆'
                            ],
                            toolbar: [
                                ['style', ['style', 'undo', 'redo']],
                                ['font', ['bold', 'underline', 'strikethrough', 'clear']],
                                ['fontname', ['color', 'fontname', 'fontsize']],
                                ['para', ['ul', 'ol', 'paragraph', 'height']],
                                ['table', ['table', 'hr']],
                                ['insert', ['link', 'picture', 'video']],
                                ['select', ['image', 'attachment']],
                                ['view', ['fullscreen', 'codeview', 'help']],
                            ],
                            buttons: {
                                image: imageButton,
                                attachment: attachmentButton,
                            },
                            dialogsInBody: true,
                            followingToolbar: false,
                            callbacks: {
                                onChange: function (contents) {
                                    $(this).val(contents);
                                    $(this).trigger('change');
                                },
                                onInit: function () {
                                },
                                onImageUpload: function (files) {
                                    var that = this;
                                    //依次上传图片
                                    for (var i = 0; i < files.length; i++) {
                                        Upload.api.send(files[i], function (data) {
                                            var url = Fast.api.cdnurl(data.url, true);
                                            $(that).summernote("insertImage", url, 'filename');
                                        });
                                    }
                                }
                            }
                        }, $(this).data("summernote-options") || {}));
                    });
                });
            }
        } catch (e) {

        }

    };
});

if (Config.modulename === 'index' && Config.controllername === 'user' && ['login', 'register'].indexOf(Config.actionname) > -1 && $("#register-form,#login-form").length > 0 && $(".social-login").length == 0) {
    $("#register-form,#login-form").append(Config.third.loginhtml || '');
}

});