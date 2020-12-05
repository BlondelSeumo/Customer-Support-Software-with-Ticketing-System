import { UploadedFile } from '../uploaded-file';

export async function readUploadedFolders(entries: WebKitEntry[]): Promise<UploadedFile[]> {
    let files = [];

    for (const key in entries) {
        const entry = entries[key];

        if ( ! entry.isDirectory) {
            files.push(await transformFileEntry(entry as WebKitFileEntry));
        } else {
            files = files.concat(await readDirRecursive(entry as WebKitDirectoryEntry));
        }
    }

    return files;
}

async function readDirRecursive(entry: WebKitDirectoryEntry, files = []) {
    const entries = await readEntries(entry);

    for (const key in entries) {
        const childEntry = entries[key];

        if (childEntry.isDirectory) {
            await readDirRecursive(childEntry as WebKitDirectoryEntry, files);
        } else {
            files.push(await transformFileEntry(childEntry as WebKitFileEntry));
        }
    }

    return files;
}

function readEntries(dir: WebKitDirectoryEntry): Promise<WebKitEntry[]> {
    return new Promise(resolve => {
        const reader = dir.createReader();
        reader.readEntries(entries => resolve(entries as any));
    });
}

function transformFileEntry(entry: WebKitFileEntry) {
    return new Promise(resolve => {
        entry.file((file: any) => {
            resolve(new UploadedFile(file as File, entry.fullPath));
        });
    });
}
