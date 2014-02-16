package org.openthessaloniki.poi;

import java.util.ArrayList;
import java.util.List;

import redstone.xmlrpc.XmlRpcStruct;

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
	private EditText editAddress1;
	private Button buttonPost;
	private Button buttonExit;
	private Button buttonIndex;
	
	/** is posting in progress */
	private boolean isPostInProgress; 

	/** post page node identifier. */
	private int nid;
	
	/** index ids. */
	private List<Integer> nids;
	
	@Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_post);
        
        // get UI controls
        editTitle = (EditText) findViewById(R.id.editTitle);
        editBody = (EditText) findViewById(R.id.editBody);
        editAddress1 = (EditText) findViewById(R.id.editAddress1);
        
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

        buttonIndex = (Button) findViewById(R.id.buttonIndex);
        buttonIndex.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				index();
			}
		});
    }

    private void index() {
    	// check if posting is in progress
    	if (isPostInProgress) {
    		return ;
    	}
    	
    	isPostInProgress = true;
    	
    	// show progress dialog
    	final ProgressDialog progressDialog = ProgressDialog.show(this, "Listing", "Getting index...", true, false);
    	    			
    	// start async task for posting to Drupal 
    	(new AsyncTask<Void, Void, Boolean>() {
    		Exception e;
    		
			@Override
			protected Boolean doInBackground(Void... params) {
				try {
					nids = DrupalConnect.getInstance().getArticles();
					List<XmlRpcStruct> articles = new ArrayList<XmlRpcStruct>();
					for(Integer nid : nids) {
						articles.add(DrupalConnect.getInstance().getArticle(nid));
					}
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
    
    private void postPage() {
    	// check if posting is in progress
    	if (isPostInProgress) {
    		return ;
    	}
    	
    	isPostInProgress = true;
    	
    	// get page title and body
    	final String title = editTitle.getText().toString();
    	final String body = editBody.getText().toString();
    	final String address1 = editAddress1.getText().toString();
    	
    	// show progress dialog
    	final ProgressDialog progressDialog = ProgressDialog.show(this, "Posting", "Posting. Please, wait.", true, false);
    	    			
    	// start async task for posting to Drupal 
    	(new AsyncTask<Void, Void, Boolean>() {
    		Exception e;
    		
			@Override
			protected Boolean doInBackground(Void... params) {
				try {
					nid = DrupalConnect.getInstance().postArticle(title, body, address1);
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
