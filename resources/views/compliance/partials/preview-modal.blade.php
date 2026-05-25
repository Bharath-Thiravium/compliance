<!-- Preview Modal -->
<div class="modal fade" id="preview-modal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog" style="max-width:90%;width:90%;margin:32px auto;">
        <div class="modal-content" style="height:85vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:#f0f2f5;border-bottom:2px solid #d9d9d9;flex-shrink:0;">
                <h5 class="modal-title" id="preview-title" style="font-weight:600;">Form Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:0;flex:1;overflow:hidden;">
                <iframe id="preview-iframe"
                        sandbox="allow-same-origin allow-scripts"
                        style="width:100%;height:100%;border:none;display:block;"
                        src="about:blank"></iframe>
            </div>
            <div class="modal-footer" style="background:#f0f2f5;border-top:1px solid #d9d9d9;flex-shrink:0;">
                <a id="preview-pdf-btn" href="#" target="_blank"
                   class="btn btn-primary btn-sm"
                   style="text-decoration:none;">⬇ Download PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
