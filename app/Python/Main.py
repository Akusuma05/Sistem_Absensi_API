import face_recognition
import cv2
from ultralytics import YOLO
import os
import sys

# Load the face detection model
model = YOLO("/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/yolov8l-face.pt")

# Path to the image
# img_path = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/detect/detect.JPG"
img_path = sys.argv[1]

# Path to the folder containing known faces
known_faces_dir = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces"
# known_faces_dir = sys.argv[2]

# Path to the folder where cropped faces will be saved (create it if it doesn't exist)
cropped_face_folder = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/cropped_faces"
os.makedirs(cropped_face_folder, exist_ok=True)

# Detect faces in the image
results = model(img_path)
boxes = results[0].boxes

# Read the original image
img = cv2.imread(img_path)

# Counter for naming the cropped images
image_count = 0

# Loop through each detected face
for box in boxes:
    # Extract coordinates
    top_left_x = int(box.xyxy.tolist()[0][0])
    top_left_y = int(box.xyxy.tolist()[0][1])
    bottom_right_x = int(box.xyxy.tolist()[0][2])
    bottom_right_y = int(box.xyxy.tolist()[0][3])

    # Crop the face
    cropped_img = img[top_left_y:bottom_right_y, top_left_x:bottom_right_x]

    # Create filename with counter
    filename = f"{cropped_face_folder}/cropped_face_{image_count}.jpg"

    # Save the cropped image
    cv2.imwrite(filename, cropped_img)

    # Increase the counter for next image
    image_count += 1

print("Cropped faces saved successfully!")

cropped_face_filenames = os.listdir(cropped_face_folder)
cropped_face_filenames.sort()  # Sort filenames alphabetically

# Load known face encodings from the "known_faces_dir" folder
known_face_encodings = []
known_face_filenames = []  # List to store filenames of known faces
for filename in os.listdir(known_faces_dir):
    if filename.endswith(".JPG") or filename.endswith(".png"):
        known_face_image = face_recognition.load_image_file(os.path.join(known_faces_dir, filename))
        known_face_image_location = face_recognition.face_locations(known_face_image)
        known_face_encoding = face_recognition.face_encodings(known_face_image, known_face_locations=known_face_image_location)[0]  # assuming one face per image
        known_face_encodings.append(known_face_encoding)
        known_face_filenames.append(filename)  # Add filename to the list


# Compare each cropped face with the known faces
for filename in os.listdir(cropped_face_folder):
    if filename.endswith(".jpg") or filename.endswith(".png"):
        cropped_face_image = face_recognition.load_image_file(os.path.join(cropped_face_folder, filename))

        # Handle cases where no faces are detected in the cropped image
        cropped_face_encodings = face_recognition.face_encodings(cropped_face_image)
        if not cropped_face_encodings:
            print(f"No faces found in {filename}. Skipping comparison.")
            continue

        # Initialize variables to store highest accuracy and corresponding index
        highest_accuracy = 0
        matching_face_index = -1

        for i, known_face_encoding in enumerate(known_face_encodings):
            # Get the face distance and calculate accuracy
            face_distance = face_recognition.face_distance(known_face_encoding.reshape(1, -1), cropped_face_encodings[0].reshape(1, -1))
            accuracy = (1 - face_distance[0]) * 100

            # Update highest accuracy and index if a better match is found with tolerance considered
            if accuracy > highest_accuracy and face_recognition.compare_faces([known_face_encoding], cropped_face_encodings[0], tolerance=0.5)[0]:
                highest_accuracy = accuracy
                matching_face_index = i

        # Check if a matching face was found (accuracy > 0)
        if matching_face_index != -1:
            # Print information about the match with highest accuracy
            print(f"Match found for {filename} vs. known face {known_face_filenames[matching_face_index]} with accuracy: {highest_accuracy:.2f}%")

# Loop through all files in the folder
for filename in os.listdir(cropped_face_folder):
    # Construct the full path to the file
    file_path = os.path.join(cropped_face_folder, filename)

    # Check if it's a file (not a directory)
    if os.path.isfile(file_path):
        # Delete the file
        os.remove(file_path)

print("All files in", cropped_face_folder, "deleted successfully!")
