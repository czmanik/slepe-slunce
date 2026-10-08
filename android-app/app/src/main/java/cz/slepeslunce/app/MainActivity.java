package cz.slepeslunce.app;

import android.Manifest;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.ActivityNotFoundException;
import android.content.ClipData;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowInsets;
import android.webkit.CookieManager;
import android.webkit.GeolocationPermissions;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.webkit.ValueCallback;
import android.widget.Button;
import android.widget.HorizontalScrollView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.core.content.FileProvider;

import java.io.File;
import java.io.IOException;
import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.Locale;
import org.json.JSONObject;

/** Mobile companion for the existing Slepé Slunce account and expedition forms. */
public final class MainActivity extends Activity {
    private static final String BASE = "https://slepeslunce.cz";
    private static final int SELECT_IMAGE = 20;
    private static final int REQUEST_LOCATION = 21;

    private WebView webView;
    private ProgressBar progress;
    private ValueCallbackHolder pendingPhoto;
    private Uri cameraPhoto;
    private GeolocationPermissions.Callback pendingLocation;
    private String pendingOrigin;
    private long lastVersionCheck;
    private int offeredVersion;

    @Override public void onCreate(Bundle state) {
        super.onCreate(state);
        getWindow().setStatusBarColor(Color.rgb(28, 26, 23));
        getWindow().setNavigationBarColor(Color.rgb(28, 26, 23));
        createScreen();
        configureWebView();
        if (state == null) webView.loadUrl(BASE + "/admin");
        else webView.restoreState(state);
        checkVersion();
    }

    private void createScreen() {
        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        root.setBackgroundColor(Color.rgb(28, 26, 23));
        if (Build.VERSION.SDK_INT >= 35) {
            root.setOnApplyWindowInsetsListener((view, insets) -> {
                android.graphics.Insets bars = insets.getInsets(WindowInsets.Type.systemBars());
                view.setPadding(0, bars.top, 0, bars.bottom);
                return insets;
            });
        }
        setContentView(root);

        TextView heading = new TextView(this);
        heading.setText("SLEPÉ SLUNCE  /  NA CESTĚ");
        heading.setTextColor(Color.rgb(244, 197, 66));
        heading.setTextSize(15);
        heading.setGravity(Gravity.CENTER_VERTICAL);
        heading.setPadding(dp(18), dp(12), dp(18), dp(12));
        root.addView(heading);

        LinearLayout nav = new LinearLayout(this);
        nav.setPadding(dp(7), 0, dp(7), dp(7));
        nav.addView(navButton("Nástěnka", "/admin"));
        nav.addView(navButton("Místo", "/admin/trasa/rychle-pridat"));
        nav.addView(navButton("Fotka", "/admin/fotka-na-mapu"));
        nav.addView(navButton("Správa", "/admin/moje-zaznamy"));
        nav.addView(navButton("Poloha", "/admin/poloha"));
        nav.addView(navButton("Mapa", "/mapa"));
        HorizontalScrollView navScroll = new HorizontalScrollView(this);
        navScroll.setHorizontalScrollBarEnabled(false);
        navScroll.addView(nav);
        root.addView(navScroll);

        progress = new ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        progress.setMax(100);
        progress.setVisibility(View.GONE);
        root.addView(progress, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(3)));

        webView = new WebView(this);
        root.addView(webView, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, 0, 1));
    }

    private Button navButton(String label, String path) {
        Button button = new Button(this);
        button.setText(label);
        button.setAllCaps(false);
        button.setTextSize(12);
        button.setTextColor(Color.WHITE);
        button.setOnClickListener(view -> webView.loadUrl(BASE + path));
        button.setLayoutParams(new LinearLayout.LayoutParams(dp(94), dp(46)));
        return button;
    }

    private void configureWebView() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true); // Filament and the map require JavaScript.
        settings.setDomStorageEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setAllowContentAccess(true); // Android photo picker content URI.
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        settings.setGeolocationEnabled(true);
        WebView.setWebContentsDebuggingEnabled(false);
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, false);

        webView.setWebViewClient(new WebViewClient() {
            @Override public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri url = request.getUrl();
                if (isTrusted(url)) return false;
                try { startActivity(new Intent(Intent.ACTION_VIEW, url)); }
                catch (ActivityNotFoundException ignored) { Toast.makeText(MainActivity.this, "Odkaz nelze otevřít.", Toast.LENGTH_SHORT).show(); }
                return true;
            }
            @Override public void onPageFinished(WebView view, String url) {
                CookieManager.getInstance().flush();
            }
        });
        webView.setWebChromeClient(new WebChromeClient() {
            @Override public void onProgressChanged(WebView view, int value) {
                progress.setProgress(value);
                progress.setVisibility(value >= 100 ? View.GONE : View.VISIBLE);
            }
            @Override public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
                if (!isTrusted(Uri.parse(origin))) { callback.invoke(origin, false, false); return; }
                if (Build.VERSION.SDK_INT < 23 || checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED
                        || checkSelfPermission(Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
                    callback.invoke(origin, true, false);
                    return;
                }
                if (pendingLocation != null) pendingLocation.invoke(pendingOrigin, false, false);
                pendingLocation = callback;
                pendingOrigin = origin;
                requestPermissions(new String[]{Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION}, REQUEST_LOCATION);
            }
            @Override public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> callback, FileChooserParams params) {
                if (pendingPhoto != null) pendingPhoto.complete(null);
                pendingPhoto = new ValueCallbackHolder(callback);
                Intent gallery = new Intent(Intent.ACTION_GET_CONTENT);
                gallery.setType("image/*");
                gallery.addCategory(Intent.CATEGORY_OPENABLE);
                Intent chooser = Intent.createChooser(gallery, "Vybrat fotografii");
                cameraPhoto = null;
                try {
                    File dir = getExternalFilesDir(Environment.DIRECTORY_PICTURES);
                    if (dir != null) {
                        File image = File.createTempFile("expedice-", ".jpg", dir);
                        cameraPhoto = FileProvider.getUriForFile(MainActivity.this, getPackageName() + ".files", image);
                        Intent camera = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
                        camera.putExtra(MediaStore.EXTRA_OUTPUT, cameraPhoto);
                        camera.setClipData(ClipData.newRawUri("expedition-photo", cameraPhoto));
                        camera.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_GRANT_WRITE_URI_PERMISSION);
                        chooser.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{camera});
                    }
                } catch (IOException | IllegalArgumentException ignored) { cameraPhoto = null; }
                try { startActivityForResult(chooser, SELECT_IMAGE); }
                catch (ActivityNotFoundException error) { pendingPhoto.complete(null); pendingPhoto = null; return false; }
                return true;
            }
        });
    }

    private boolean isTrusted(Uri url) {
        return "https".equalsIgnoreCase(url.getScheme()) && "slepeslunce.cz".equalsIgnoreCase(url.getHost());
    }

    @Override protected void onResume() {
        super.onResume();
        if (webView != null && System.currentTimeMillis() - lastVersionCheck > 24 * 60 * 60 * 1000L) checkVersion();
    }

    private void checkVersion() {
        lastVersionCheck = System.currentTimeMillis();
        new Thread(() -> {
            HttpURLConnection connection = null;
            try {
                connection = (HttpURLConnection) new URL(BASE + "/app/version.json").openConnection();
                connection.setConnectTimeout(5000);
                connection.setReadTimeout(5000);
                connection.setRequestProperty("Accept", "application/json");
                if (connection.getResponseCode() != 200) return;
                ByteArrayOutputStream output = new ByteArrayOutputStream();
                try (InputStream input = connection.getInputStream()) {
                    byte[] buffer = new byte[4096];
                    int count;
                    while ((count = input.read(buffer)) != -1) output.write(buffer, 0, count);
                }
                byte[] bytes = output.toByteArray();
                JSONObject release = new JSONObject(new String(bytes, StandardCharsets.UTF_8));
                int latest = release.optInt("version_code", 0);
                int installed = getPackageManager().getPackageInfo(getPackageName(), 0).versionCode;
                if (latest <= installed || latest <= offeredVersion || !isTrusted(Uri.parse(release.optString("download_url")))) return;
                runOnUiThread(() -> {
                    if (isFinishing() || isDestroyed()) return;
                    offeredVersion = latest;
                    new AlertDialog.Builder(this)
                        .setTitle("Nová verze aplikace")
                        .setMessage("Je dostupná verze " + release.optString("version_name") + ". Chcete otevřít stránku pro stažení?")
                        .setPositiveButton("Otevřít /app", (dialog, which) -> startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(BASE + "/app"))))
                        .setNegativeButton("Později", null)
                        .show();
                });
            } catch (Exception ignored) { /* Offline mode: try again on the next launch. */ }
            finally { if (connection != null) connection.disconnect(); }
        }).start();
    }

    @Override protected void onActivityResult(int request, int result, Intent data) {
        super.onActivityResult(request, result, data);
        if (request != SELECT_IMAGE || pendingPhoto == null) return;
        Uri selected = null;
        if (result == RESULT_OK) {
            if (data != null && data.getData() != null) selected = data.getData();
            else if (data != null && data.getClipData() != null && data.getClipData().getItemCount() > 0)
                selected = data.getClipData().getItemAt(0).getUri();
            else selected = cameraPhoto;
        }
        if (selected == null) {
            pendingPhoto.complete(null);
            pendingPhoto = null;
            cameraPhoto = null;
            return;
        }
        final Uri source = selected;
        final ValueCallbackHolder callback = pendingPhoto;
        Toast.makeText(this, "Připravuji fotografii pro odeslání…", Toast.LENGTH_SHORT).show();
        new Thread(() -> {
            PhotoPreprocessor.Result processed = null;
            String failure = null;
            try { processed = PhotoPreprocessor.process(getApplicationContext(), source); }
            catch (Exception error) { failure = error.getMessage(); }
            final PhotoPreprocessor.Result resultPhoto = processed;
            final String errorMessage = failure;
            runOnUiThread(() -> {
                if (pendingPhoto != callback || isFinishing() || isDestroyed()) return;
                if (resultPhoto == null) {
                    Toast.makeText(this, errorMessage == null ? "Fotografii se nepodařilo zmenšit." : errorMessage, Toast.LENGTH_LONG).show();
                    callback.complete(null);
                } else {
                    if (resultPhoto.gps != null) {
                        String script = String.format(Locale.US, "window.applyPhotoExifLocation?.(%.7f,%.7f);", resultPhoto.gps[0], resultPhoto.gps[1]);
                        webView.evaluateJavascript(script, null);
                    }
                    callback.complete(new Uri[]{resultPhoto.uri});
                }
                pendingPhoto = null;
            });
        }).start();
        cameraPhoto = null;
    }

    @Override public void onRequestPermissionsResult(int code, String[] permissions, int[] results) {
        super.onRequestPermissionsResult(code, permissions, results);
        if (code == REQUEST_LOCATION && pendingLocation != null) {
            boolean granted = false;
            for (int result : results) granted |= result == PackageManager.PERMISSION_GRANTED;
            pendingLocation.invoke(pendingOrigin, granted, false);
            pendingLocation = null;
            pendingOrigin = null;
        }
    }

    @Override public void onBackPressed() {
        if (webView.canGoBack()) webView.goBack();
        else super.onBackPressed();
    }

    @Override protected void onSaveInstanceState(Bundle state) {
        webView.saveState(state);
        super.onSaveInstanceState(state);
    }

    @Override protected void onDestroy() {
        if (pendingPhoto != null) pendingPhoto.complete(null);
        if (pendingLocation != null) pendingLocation.invoke(pendingOrigin, false, false);
        webView.destroy();
        super.onDestroy();
    }

    private int dp(int value) { return (int) (value * getResources().getDisplayMetrics().density + .5f); }

    private static final class ValueCallbackHolder {
        private final android.webkit.ValueCallback<Uri[]> callback;
        ValueCallbackHolder(android.webkit.ValueCallback<Uri[]> callback) { this.callback = callback; }
        void complete(Uri[] value) { callback.onReceiveValue(value); }
    }
}
