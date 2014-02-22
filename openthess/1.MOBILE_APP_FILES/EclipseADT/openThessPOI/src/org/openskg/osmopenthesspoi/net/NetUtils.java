package org.openskg.osmopenthesspoi.net;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;

import android.os.AsyncTask;

import com.squareup.okhttp.OkHttpClient;

public class NetUtils {
	public interface NetResultHandler {
		void onResult(String result);
	}

	public static void getAsync(URL url, final NetResultHandler netResultHandler) {
		new AsyncTask<URL, Void, String>() {
			@Override
			protected String doInBackground(URL... params) {
				try {
					return NetUtils.get(params[0]);
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				return null;
			}

			@Override
			protected void onPostExecute(String result) {
				netResultHandler.onResult(result);

				super.onPostExecute(result);
			}
		}.execute(url);
	}

	public static String get(URL url) throws IOException {
		OkHttpClient client = new OkHttpClient();
		HttpURLConnection connection = client.open(url);
		InputStream in = null;
		try {
			// Read the response.
			in = connection.getInputStream();
			byte[] response = readFully(in);
			return new String(response, "UTF-8");
		} finally {
			if (in != null)
				in.close();
		}
	}

	private static byte[] readFully(InputStream in) throws IOException {
		ByteArrayOutputStream out = new ByteArrayOutputStream();
		byte[] buffer = new byte[1024];
		for (int count; (count = in.read(buffer)) != -1;) {
			out.write(buffer, 0, count);
		}
		return out.toByteArray();
	}

}
