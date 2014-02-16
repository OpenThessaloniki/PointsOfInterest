package org.openthessaloniki.poi;

import android.app.Activity;
import android.app.ProgressDialog;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.EditText;

public class PostActivity extends Activity {
	// UI controls
    private EditText editTitle;
	private EditText editBody;
	private Button buttonPost;
	private Button buttonExit;
	
	/** is posting in progress */
	private boolean isPostInProgress; 

	/** post page node identifier. */
	private int nid;
	
	@Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_post);
        
        // get UI controls
        editTitle = (EditText) findViewById(R.id.editTitle);
        editBody = (EditText) findViewById(R.id.editBody);
        
        buttonPost = (Button) findViewById(R.id.buttonPost);
        buttonPost.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				postPage();
			}
		});
        
        buttonExit = (Button) findViewById(R.id.buttonExit);
        buttonExit.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				exit();
			}
		});
    }

    private void postPage() {
    	// check if posting is in progress
    	if (isPostInProgress) {
    		return ;
    	}
    	
    	isPostInProgress = true;
    	
    	// get page title and body
    	final String title = editTitle.getText().toString();
    	final String body = editBody.getText().toString();
    	
    	// show progress dialog
    	final ProgressDialog progressDialog = ProgressDialog.show(this, "Posting", "Posting. Please, wait.", true, false);
    	    			
    	// start async task for posting to Drupal 
    	(new AsyncTask<Void, Void, Boolean>() {
    		Exception e;
    		
			@Override
			protected Boolean doInBackground(Void... params) {
				try {
					nid = DrupalConnect.getInstance().postArticle(title, body);
					return true;
				}
				catch (Exception e) {
					this.e = e;
					return false;
				}
			}

			@Override
			protected void onPostExecute(Boolean result) {
				super.onPostExecute(result);

				progressDialog.dismiss();
				
				if (result) {
					GUIHelper.showMessage(PostActivity.this, "Success!", "Post OK");
				}
				else {
					GUIHelper.showError(PostActivity.this, "Post is failed. "+e.getMessage());
					isPostInProgress = false;
				}
			}
    		
    	}).execute();
	}
    
    @SuppressWarnings("unused")
	private void deletePage() {
    	// check if there is page
    	if (nid == 0) {
    		return ; 
    	}
    	
    	(new AsyncTask<Void, Void, Boolean>() {
			@Override
			protected Boolean doInBackground(Void... params) {
				try {
					DrupalConnect.getInstance().deletePage(nid);
					nid = 0;
				} 
				catch (Exception e) {
					e.printStackTrace();
				}
				return null;
			}
		}).execute();
    }
    
    private void exit() {
    	(new AsyncTask<Void, Void, Boolean>() {
			@Override
			protected Boolean doInBackground(Void... params) {
				try {
					DrupalConnect.getInstance().logout();
				} 
				catch (Exception e) {
					e.printStackTrace();
				} 
				return null;
			}

			@Override
			protected void onPostExecute(Boolean result) {
				super.onPostExecute(result);
				
				PostActivity.this.finish();
			}
    	}).execute();
    }
}
