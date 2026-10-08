package cz.slepeslunce.app;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Matrix;
import android.media.ExifInterface;
import android.net.Uri;

import androidx.core.content.FileProvider;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;

/** Decode a sampled image, correct its orientation, and return a private JPEG under 5 MiB. */
final class PhotoPreprocessor {
    static final long MAX_BYTES = 5L * 1024 * 1024;
    private static final long TARGET_BYTES = MAX_BYTES - 64 * 1024;
    private static final int MAX_EDGE = 2560;

    static final class Result {
        final Uri uri;
        final float[] gps;
        Result(Uri uri, float[] gps) { this.uri = uri; this.gps = gps; }
    }

    static Result process(Context context, Uri source) throws IOException {
        int orientation = ExifInterface.ORIENTATION_NORMAL;
        float[] gps = new float[2];
        String latitude = null, latitudeRef = null, longitude = null, longitudeRef = null;
        try (InputStream stream = context.getContentResolver().openInputStream(source)) {
            if (stream == null) throw new IOException("Fotografii se nepodařilo otevřít.");
            ExifInterface exif = new ExifInterface(stream);
            orientation = exif.getAttributeInt(ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL);
            if (!exif.getLatLong(gps)) gps = null;
            else {
                latitude = exif.getAttribute(ExifInterface.TAG_GPS_LATITUDE);
                latitudeRef = exif.getAttribute(ExifInterface.TAG_GPS_LATITUDE_REF);
                longitude = exif.getAttribute(ExifInterface.TAG_GPS_LONGITUDE);
                longitudeRef = exif.getAttribute(ExifInterface.TAG_GPS_LONGITUDE_REF);
            }
        }

        BitmapFactory.Options bounds = new BitmapFactory.Options();
        bounds.inJustDecodeBounds = true;
        try (InputStream stream = context.getContentResolver().openInputStream(source)) {
            BitmapFactory.decodeStream(stream, null, bounds);
        }
        if (bounds.outWidth <= 0 || bounds.outHeight <= 0) throw new IOException("Tento formát fotografie se nepodařilo zpracovat.");
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inSampleSize = 1;
        while (Math.max(bounds.outWidth / options.inSampleSize, bounds.outHeight / options.inSampleSize) > MAX_EDGE * 2)
            options.inSampleSize *= 2;
        Bitmap bitmap;
        try (InputStream stream = context.getContentResolver().openInputStream(source)) {
            bitmap = BitmapFactory.decodeStream(stream, null, options);
        }
        if (bitmap == null) throw new IOException("Fotografii se nepodařilo přečíst.");
        Bitmap oriented = orient(bitmap, orientation);
        if (oriented != bitmap) bitmap.recycle();
        bitmap = oriented;
        int edge = Math.max(bitmap.getWidth(), bitmap.getHeight());
        if (edge > MAX_EDGE) {
            Bitmap smaller = Bitmap.createScaledBitmap(bitmap,
                Math.max(1, Math.round(bitmap.getWidth() * MAX_EDGE / (float) edge)),
                Math.max(1, Math.round(bitmap.getHeight() * MAX_EDGE / (float) edge)), true);
            bitmap.recycle();
            bitmap = smaller;
        }

        byte[] encoded = null;
        for (int scale = 0; scale < 7; scale++) {
            for (int quality = 88; quality >= 44; quality -= 11) {
                ByteArrayOutputStream out = new ByteArrayOutputStream();
                if (!bitmap.compress(Bitmap.CompressFormat.JPEG, quality, out)) throw new IOException("Fotografii se nepodařilo zmenšit.");
                encoded = out.toByteArray();
                if (encoded.length <= TARGET_BYTES) break;
            }
            if (encoded != null && encoded.length <= TARGET_BYTES) break;
            Bitmap smaller = Bitmap.createScaledBitmap(bitmap, Math.max(1, bitmap.getWidth() * 4 / 5),
                Math.max(1, bitmap.getHeight() * 4 / 5), true);
            bitmap.recycle();
            bitmap = smaller;
        }
        bitmap.recycle();
        if (encoded == null || encoded.length > TARGET_BYTES) throw new IOException("Fotografie je i po zmenšení příliš velká.");

        File destination = File.createTempFile("upload-", ".jpg", context.getCacheDir());
        try (FileOutputStream out = new FileOutputStream(destination)) { out.write(encoded); }
        if (gps != null && latitude != null && latitudeRef != null && longitude != null && longitudeRef != null) {
            ExifInterface outputExif = new ExifInterface(destination.getAbsolutePath());
            outputExif.setAttribute(ExifInterface.TAG_GPS_LATITUDE, latitude);
            outputExif.setAttribute(ExifInterface.TAG_GPS_LATITUDE_REF, latitudeRef);
            outputExif.setAttribute(ExifInterface.TAG_GPS_LONGITUDE, longitude);
            outputExif.setAttribute(ExifInterface.TAG_GPS_LONGITUDE_REF, longitudeRef);
            outputExif.saveAttributes();
        }
        if (destination.length() > MAX_BYTES) {
            destination.delete();
            throw new IOException("Fotografie překračuje limit 5 MB.");
        }
        return new Result(FileProvider.getUriForFile(context, context.getPackageName() + ".files", destination), gps);
    }

    private static Bitmap orient(Bitmap image, int orientation) {
        Matrix matrix = new Matrix();
        switch (orientation) {
            case ExifInterface.ORIENTATION_FLIP_HORIZONTAL: matrix.setScale(-1, 1); break;
            case ExifInterface.ORIENTATION_ROTATE_180: matrix.setRotate(180); break;
            case ExifInterface.ORIENTATION_FLIP_VERTICAL: matrix.setScale(1, -1); break;
            case ExifInterface.ORIENTATION_TRANSPOSE: matrix.setRotate(90); matrix.postScale(-1, 1); break;
            case ExifInterface.ORIENTATION_ROTATE_90: matrix.setRotate(90); break;
            case ExifInterface.ORIENTATION_TRANSVERSE: matrix.setRotate(270); matrix.postScale(-1, 1); break;
            case ExifInterface.ORIENTATION_ROTATE_270: matrix.setRotate(270); break;
            default: return image;
        }
        return Bitmap.createBitmap(image, 0, 0, image.getWidth(), image.getHeight(), matrix, true);
    }
}
