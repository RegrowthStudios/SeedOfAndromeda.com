!function ($, window, document, _undefined) {
    XenForo.Markforumread = function ($nodeIcon) { this.__construct($nodeIcon); };
    XenForo.Markforumread.prototype = {
        __construct: function ($nodeIcon) {
            if (this.url = $nodeIcon.data('markreadlink')) {
                this.$link = $nodeIcon.dblclick($.context(this, 'sendRequest'));
                this.$link.css('cursor', 'pointer');
                this.$container = $($nodeIcon.parent());
            }
        },
        sendRequest: function (e) {
            e.preventDefault();
            XenForo.ajax(
                this.url,
                { '_xfConfirm': 1 },
                $.context(this, 'markRead')
            );
        },
        markRead: function (ajaxData, textStatus) {
            if (XenForo.hasResponseError(ajaxData)) {
                return false;
            }
            this.$container.removeClass('unread');
        }
    };
    XenForo.register('span.nodeIcon', 'XenForo.Markforumread');
}
(jQuery, this, document);