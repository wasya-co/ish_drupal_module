
import * as THREE from 'three';

/**
 * Usage: logg(someObject, 'label')
 *
 * This development-grade logger can be used instead of console.log() with some advantages:
 * * It encourages consistent labeling of logs. By labeling each log line, you can have dozens of log lines
 * written per action, and still know which log line comes from where.
 * The recommended label is the component name, or function name.
 * * If the label is present, the logged object is placed in the window, allowing you to inspect it in the console. The
 * label becomes the name of the object (stripped to [0-9a-zA-Z\-_] chars). If you're logging a function, you can execute it.
 * If you log more than one thing, they can interact, allowing you to validate control flow.
 * * the logger can be turned off by making this function simply return.
**/
const logg = (a, b="", c=null) => {
  if ('undefined' === typeof window) { return }

  c = "string" === typeof c ? c : b.replace(/\W/g, "");
  if (c.length > 0) {
    window[c] = a;
  }
  console.log(`+++ ${b}:`, a); // eslint-disable-line no-console
};

function fitCameraToObject_1(camera, object, controls, offset = 1.2) {
  const box = new THREE.Box3().setFromObject(object);
  const size = box.getSize(new THREE.Vector3());
  const center = box.getCenter(new THREE.Vector3());

  const maxDim = Math.max(size.x, size.y, size.z);
  const fov = camera.fov * (Math.PI / 180);
  let cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2));
  cameraZ *= offset;

  camera.position.set(center.x, center.y, center.z + cameraZ);
  camera.near = cameraZ / 100;
  camera.far = cameraZ * 100;
  camera.updateProjectionMatrix();

  if (controls) {
    controls.target.copy(center);
    controls.update();
  }

  return maxDim
}

function fitCameraToObject(camera, object, controls, offset = 1.2, heightFactor = 0.6) {
  const box = new THREE.Box3().setFromObject(object);
  const size = box.getSize(new THREE.Vector3());
  const center = box.getCenter(new THREE.Vector3());

  const maxDim = Math.max(size.x, size.y, size.z);
  const fov = camera.fov * (Math.PI / 180);

  let cameraDistance = Math.abs(maxDim / 2 / Math.tan(fov / 2));
  cameraDistance *= offset;

  // Raise camera upward and pull it back
  const cameraHeight = maxDim * heightFactor;

  camera.position.set(
    center.x,
    center.y + cameraHeight,
    center.z + cameraDistance
  );

  // Make camera look down at the object
  camera.lookAt(center);

  camera.near = cameraDistance / 100;
  camera.far = cameraDistance * 100;
  camera.updateProjectionMatrix();

  if (controls) {
    controls.target.copy(center);
    controls.update();
  }

  return maxDim;
}

export {
  fitCameraToObject,
  logg,
}
