import Element from './component'

function App() {
  return (
    <div className="App">
      <header className="App-header">
          <Element
              title={"SpongeBob up"}
              iw={1283}
              ih={960}
              filename={"assets/spongebob.png"}
              videoname={"assets/spongebob-up.mp4"}
              direction={"up"}/>

          <Element
              title={"SpongeBob down"}
              iw={1283}
              ih={960}
              filename={"assets/spongebob.png"}
              videoname={"assets/spongebob-down.mp4"}
              direction={"down"}/>


      </header>
    </div>
  );
}

export default App;
