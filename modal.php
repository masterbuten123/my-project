
<!-- ================= ADD POST MODAL ================= -->
<div class="modal fade" id="addPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="addPostForm" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Create Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Post Content -->
                <textarea name="post_content" required class="form-control mb-3" placeholder="What's on your mind?" rows="4"></textarea>
                
                <!-- Image Upload -->
                <div class="text-center mb-2">
                    <img id="addPreviewImage" class="img-fluid rounded d-none mb-2" style="max-height:200px;">
                    <div>
                        <button type="button" id="addUploadBtn" class="btn btn-sm btn-secondary me-2">Upload Image</button>
                        <input type="file" id="addPostImage" name="post_image" class="d-none" accept="image/*">
                        <span id="addFileName" class="small text-muted"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Post</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT POST MODAL ================= -->
<div class="modal fade" id="editPostModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="editPostForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Post Content -->
        <div class="mb-3">
          <label for="editPostContent" class="form-label">Post Content</label>
          <textarea id="editPostContent" name="post_content" required class="form-control" rows="4" placeholder="What's on your mind?"></textarea>
        </div>

        <!-- Image Upload Area -->
        <div class="mb-3">
          <label class="form-label">Image (optional)</label>
          <div id="editImageWrapper" class="position-relative border rounded p-2 d-flex justify-content-center align-items-center" style="height:150px; cursor:pointer; background:#f8f9fa;">
            <span id="editUploadText" class="text-muted">Click or drag an image here</span>
            <img id="editPreviewImage" class="img-fluid position-absolute h-100 d-none" style="object-fit:contain;">
            <button type="button" id="editRemoveBtn" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 d-none">
              <i class="bi bi-x-lg"></i>
            </button>
            <input type="file" id="editPostImage" name="post_image" class="d-none" accept="image/*">
          </div>
          <small id="editFileName" class="text-muted"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Update Post</button>
      </div>
    </form>
  </div>
</div>


<!-- ================= REPORT POST MODAL ================= -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reportForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reportPostId" name="post_id">
                <select id="reportReason" name="reason" class="form-select form-select-sm mb-2">
                    <option value="">Select reason</option>
                    <option value="Spam">Spam</option>
                    <option value="Inappropriate">Inappropriate</option>
                    <option value="Other">Other</option>
                </select>
                <div id="otherReasonBox" class="d-none">
                    <input type="text" id="otherReason" class="form-control" placeholder="Specify reason">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Report</button>
            </div>
        </form>
    </div>
</div>

