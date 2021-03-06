<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Notifications\StandardEmail;
use App\Notifications\UserInviteEmail;
use Exception;
use Illuminate\Support\Facades\Notification;

class InvitesController extends Controller
{
    /**
     * Delete the invitation.
     *
     * @param  \App\Models\Invite  $appModelsInvite
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revoke(Invite $invite)
    {
        try {
            $email = $invite->email;
            $subject = 'Invitation Deleted';
            $message = "Your invitation from {$invite->from->name} has been removed.";

            Notification::route('mail', $invite->email)
                ->notify(new StandardEmail($email, $subject, $message));

            $invite->delete();

            return redirect()->back()->withMessage('Invitation was revoked');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['Invitation was unable to be revoked']);
        }
    }

    /**
     * Resend the invitation.
     *
     * @param  \App\Models\Invite  $appModelsInvite
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Invite $invite)
    {
        try {
            Notification::route('mail', $invite->email)
                ->notify(new UserInviteEmail(
                    $invite->email,
                    $invite->from,
                    $invite->message,
                    $invite->token
                ));

            return redirect()->back()->withMessage('Invitation was resent');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['Invitation was unable to be resent']);
        }
    }
}
