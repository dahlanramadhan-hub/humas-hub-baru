<?php

namespace App\Policies;

use App\Models\ContentAsset;
use App\Models\Content;
use App\Models\Draft;
use App\Models\User;

class ContentPolicy
{
    private function owns(User $u, Content $c): bool
    {
        return $u->isPengaju() && (int) $c->requester_id === (int) $u->id;
    }

    private function assigned(User $u, Content $c): bool
    {
        return $u->isEditor() && (int) $c->editor_id === (int) $u->id;
    }

    public function viewAny(User $u): bool
    {
        return true;
    }

    public function create(User $u): bool
    {
        return $u->isSuperAdmin() || $u->isPengaju();
    }

    public function view(User $u, Content $c): bool
    {
        return match (true) {
            $u->isSuperAdmin() => true,
            $u->isPengaju()    => $this->owns($u, $c),
            $u->isEditor()     => $this->assigned($u, $c),
            $u->isPublisher()  => $c->isFinished(),   // belum ACC = tidak terlihat
            default            => false,
        };
    }

    /** Ubah info + kelola bahan/referensi. */
    public function update(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $this->owns($u, $c)) && $c->isBeforeApproval();
    }

    public function manageAssets(User $u, Content $c): bool
    {
        return $this->update($u, $c);
    }

    /** Lihat/download bahan, referensi, dan semua draft. Publisher TIDAK boleh. */
    public function viewAssets(User $u, Content $c): bool
    {
        return $u->isSuperAdmin() || $this->owns($u, $c) || $this->assigned($u, $c);
    }

    public function viewDrafts(User $u, Content $c): bool
    {
        return $this->viewAssets($u, $c);
    }

    public function uploadDraft(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $this->assigned($u, $c))
            && in_array($c->status, ['Belum Dikerjakan', 'Revisi', 'Menunggu Review'], true);
    }

    /** Komentar revisi + ACC. */
    public function review(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $this->owns($u, $c)) && $c->status === 'Menunggu Review';
    }

    public function reopen(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $this->owns($u, $c)) && $c->status === 'Approved';
    }

    public function editCaption(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $this->owns($u, $c) || $this->assigned($u, $c)) && $c->isBeforeApproval();
    }

    /** Jadwal + publish. */
    public function publish(User $u, Content $c): bool
    {
        return ($u->isSuperAdmin() || $u->isPublisher())
            && in_array($c->status, ['Approved', 'Publish Terjadwal'], true);
    }

    public function downloadFinal(User $u, Content $c): bool
    {
        return $this->view($u, $c) && $c->isFinished() && $c->final_draft_id !== null;
    }

    public function downloadDraft(User $u, Content $c, Draft $d): bool
    {
        return (int) $d->content_id === (int) $c->id && $this->viewDrafts($u, $c);
    }

    public function downloadAsset(User $u, Content $c, ContentAsset $a): bool
    {
        return (int) $a->content_id === (int) $c->id && $this->viewAssets($u, $c);
    }
        /** Hapus konten. */
    public function delete(User $u, Content $c): bool
    {
        return $u->isSuperAdmin() || ($this->owns($u, $c) && $c->isBeforeApproval());
    }

    /** Hapus draft yang salah unggah (belum dikomentari). */
    public function deleteDraft(User $u, Content $c, Draft $d): bool
    {
        return (int) $d->content_id === (int) $c->id
            && $this->uploadDraft($u, $c)
            && ! $d->comments()->exists();
    }
}