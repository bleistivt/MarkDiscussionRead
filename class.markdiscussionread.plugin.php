<?php if (!defined('APPLICATION')) exit();

$PluginInfo['MarkDiscussionRead'] = array(
    'Name' => 'MarkDiscussionRead',
    'Description' => 'Selectively mark discussions as read.',
    'Version' => '1.0',
    'MobileFriendly' => true,
    'Author' => 'Bleistivt'
);

class MarkDiscussionReadPlugin extends Gdn_Plugin {

    public function DiscussionController_MarkRead_Create($Sender, $Args) {
        if (!$Sender->Request->IsAuthenticatedPostBack()) {
            throw PermissionException('Javascript');
        }

        $DiscussionModel = new DiscussionModel();
        $Discussion = $DiscussionModel->GetID(val(0, $Args));
        if (!$Discussion) {
            throw NotFoundException('Discussion');
        }

        $CountComments = $DiscussionModel->CountComments;
        $CommentModel = new CommentModel();
        $CommentModel->SetWatch($Discussion, $CountComments, $CountComments, $CountComments);

        $Sender->JsonTarget("#Discussion_{$Discussion->DiscussionID}", 'New Unread', 'RemoveClass');
        $Sender->JsonTarget("#Discussion_{$Discussion->DiscussionID} .NewCommentCount", null, 'Remove');
        $Sender->JsonTarget("#Discussion_{$Discussion->DiscussionID}", 'Read', 'AddClass');

        $Discussion->CountUnreadComments = 0;
        $Sender->SendOptions($Discussion);

        $Sender->Render('Blank', 'Utility', 'Dashboard');
    }

    public function Base_DiscussionOptions_Handler($Sender) {
        if (!Gdn::Session()->IsValid() || !isset($Sender->Options)) {
            return;
        }

        $Discussion = $Sender->EventArguments['Discussion'];
        if (!$Discussion->CountUnreadComments > 0) {
            return;
        }

        $Option = array(
            'Label' => T('Mark as read'),
            'Url' => '/discussion/markread/'.$Discussion->DiscussionID,
            'Class' => 'MarkRead Hijack'
        );

        $Sender->Options .= Wrap(Anchor($Option['Label'], $Option['Url'], $Option['Class']), 'li');
    }

}
