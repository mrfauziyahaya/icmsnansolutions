// When an image is dropped/pasted into a Trix editor, upload it to the server
// and point the attachment at the stored file's URL (instead of inlining a
// huge data-URI in the post body).
document.addEventListener('trix-attachment-add', function (event) {
    const attachment = event.attachment;

    if (!attachment.file) {
        return;   // e.g. undo re-adding an already-uploaded attachment
    }

    const endpoint = document.querySelector('meta[name="blog-attachment-url"]')?.content;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!endpoint) {
        return;
    }

    const body = new FormData();
    body.append('file', attachment.file);

    fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
        body,
    })
        .then((res) => {
            if (!res.ok) throw new Error('upload failed');
            return res.json();
        })
        .then((data) => attachment.setAttributes({ url: data.url, href: data.url }))
        .catch(() => attachment.remove());
});
